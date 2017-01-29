<?php
/**
 * Implementation of the `coveralls\Job` class.
 */
namespace coveralls;

/**
 * Represents the coverage data from a single run of a test suite.
 */
class Job implements \JsonSerializable {

  /**
   * @var string The current SHA of the commit being built to override the `git` parameter.
   */
  private $commitSha = '';

  /**
   * @var GitData The Git data that can be used to display more information to users.
   */
  private $git;

  /**
   * @var bool Value indicating whether the build will not be considered done until a webhook has been sent to Coveralls.
   */
  private $isParallel = false;

  /**
   * @var string The secret token for the repository.
   */
  private $repoToken = '';

  /**
   * @var \DateTime The timestamp of when the job ran.
   */
  private $runAt;

  /**
   * @var string The unique identifier of the job on the CI service.
   */
  private $serviceJobId = '';

  /**
   * @var string The CI service or other environment in which the test suite was run.
   */
  private $serviceName = '';

  /**
   * @var string The build number.
   */
  private $serviceNumber = '';

  /**
   * @var string The associated pull request identifier of the build.
   */
  private $servicePullRequest = '';

  /**
   * @var \ArrayObject The list of source files.
   */
  private $sourceFiles;

  /**
   * Initializes a new instance of the class.
   * @param Configuration $config The job configuration.
   * @param SourceFile[] $sourceFiles The list of source files.
   */
  public function __construct(Configuration $config = null, array $sourceFiles = []) {
    $this->sourceFiles = new \ArrayObject($sourceFiles);

    if ($config) {
      $hasGitData = count(array_filter($config->getKeys(), function($key) {
        return $key == 'service_branch' || mb_substr($key, 0, 4) == 'git_';
      })) > 0;

      if (!$hasGitData) $this->setCommitSha($config['commit_sha'] ?: '');
      else {
        $commit = new GitCommit($config['commit_sha'] ?: '', $config['git_message'] ?: '');
        $commit->setAuthorEmail($config['git_author_email'] ?: '');
        $commit->setAuthorName($config['git_author_name'] ?: '');
        $commit->setCommitterEmail($config['git_committer_email'] ?: '');
        $commit->setCommitterName($config['git_committer_email'] ?: '');

        $this->setGit(new GitData($commit, $config['service_branch'] ?: ''));
      }

      $this->setParallel($config['parallel'] == 'true');
      $this->setRepoToken($config['repo_token'] ?: ($config['repo_secret_token'] ?: ''));
      $this->setRunAt($config['run_at'] ? new \DateTime($config['run_at']) : null);
      $this->setServiceJobId($config['service_job_id'] ?: '');
      $this->setServiceName($config['service_name'] ?: '');
      $this->setServiceNumber($config['service_number'] ?: '');
      $this->setServicePullRequest($config['service_pull_request'] ?: '');
    }
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return static::class." $json";
  }

  /**
   * Creates a new job from the specified JSON map.
   * @param mixed $map A JSON map representing a job.
   * @return Job The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJSON($map) {
    $transform = function(array $files) {
      return array_filter(array_map(function($item) { return SourceFile::fromJSON($item); }, $files));
    };

    if (is_array($map)) $map = (object) $map;
    return !is_object($map) ? null : new static(
      new Configuration(get_object_vars($map)),
      isset($map->source_files) && is_array($map->source_files) ? $transform($map->source_files) : []
    );
  }

  /**
   * Gets the current SHA of the commit being built to override the `git` parameter.
   * @return string The SHA of the commit being built.
   */
  public function getCommitSha(): string {
    return $this->commitSha;
  }

  /**
   * Get the Git data that can be used to display more information to users.
   * @return GitData The Git data that can be used to display more information to users.
   */
  public function getGit() {
    return $this->git;
  }

  /**
   * Gets the secret token for the repository.
   * @return string The secret token for the repository.
   */
  public function getRepoToken(): string {
    return $this->repoToken;
  }

  /**
   * Gets the timestamp of when the job ran.
   * @return \DateTime The timestamp of when the job ran.
   */
  public function getRunAt() {
    return $this->runAt;
  }

  /**
   * Gets the unique identifier of the job on the CI service.
   * @return string The unique identifier of the job on the CI service.
   */
  public function getServiceJobId(): string {
    return $this->serviceJobId;
  }

  /**
   * Gets the CI service or other environment in which the test suite was run.
   * @return string The CI service or other environment in which the test suite was run.
   */
  public function getServiceName(): string {
    return $this->serviceName;
  }

  /**
   * Gets the build number.
   * @return string The build number.
   */
  public function getServiceNumber(): string {
    return $this->serviceNumber;
  }

  /**
   * Gets the associated pull request identifier of the build.
   * @return string The associated pull request identifier of the build.
   */
  public function getServicePullRequest(): string {
    return $this->servicePullRequest;
  }

  /**
   * Gets the list of source files.
   * @return \ArrayObject The source files.
   */
  public function getSourceFiles(): \ArrayObject {
    return $this->sourceFiles;
  }

  /**
   * Gets a value indicating whether the build will not be considered done until a webhook has been sent to Coveralls.
   * @return bool `true` if the build will not be considered done until a webhook has been sent to Coverall, otherwise `false`.
   */
  public function isParallel(): bool {
    return $this->isParallel;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    $map = new \stdClass();

    if ($repoToken = $this->getRepoToken()) $map->repo_token = $repoToken;
    if ($serviceName = $this->getServiceName()) $map->service_name = $serviceName;
    if ($serviceNumber = $this->getServiceNumber()) $map->service_number = $serviceNumber;
    if ($serviceJobId = $this->getServiceJobId()) $map->service_job_id = $serviceJobId;
    if ($servicePullRequest = $this->getServicePullRequest()) $map->service_pull_request = $servicePullRequest;

    $map->source_files = array_map(function(SourceFile $item) { return $item->jsonSerialize(); }, $this->getSourceFiles()->getArrayCopy());
    if ($this->isParallel()) $map->parallel = true;
    if ($git = $this->getGit()) $map->git = $git->jsonSerialize();
    if ($commitSha = $this->getCommitSha()) $map->commit_sha = $commitSha;
    if ($runAt = $this->getRunAt()) $map->run_at = $runAt->format('c');

    return $map;
  }

  /**
   * Sets the current SHA of the commit being built to override the `git` parameter.
   * @param string $value The new SHA of the commit being built.
   * @return Job This instance.
   */
  public function setCommitSha(string $value): self {
    $this->commitSha = $value;
    return $this;
  }

  /**
   * Sets the Git data that can be used to display more information to users.
   * @param GitData $value The new Git data.
   * @return Job This instance.
   */
  public function setGit(GitData $value = null): self {
    $this->git = $value;
    return $this;
  }

  /**
   * Sets a value indicating whether the build will not be considered done until a webhook has been sent to Coveralls.
   * @param bool $value `true` if the build will not be considered done until a webhook has been sent to Coverall, otherwise `false`.
   * @return Job This instance.
   */
  public function setParallel(bool $value): self {
    $this->isParallel = $value;
    return $this;
  }

  /**
   * Sets the secret token for the repository.
   * @param string $value The new secret token.
   * @return Job This instance.
   */
  public function setRepoToken(string $value): self {
    $this->repoToken = $value;
    return $this;
  }

  /**
   * Sets the timestamp of when the job ran.
   * @param \DateTime $value The new timestamp.
   * @return Job This instance.
   */
  public function setRunAt(\DateTime $value = null): self {
    $this->runAt = $value;
    return $this;
  }

  /**
   * Gets the unique identifier of the job on the CI service.
   * @param string $value The new unique identifier of the job on the CI service.
   * @return Job This instance.
   */
  public function setServiceJobId(string $value): self {
    $this->serviceJobId = $value;
    return $this;
  }

  /**
   * Gets the CI service or other environment in which the test suite was run.
   * @param string $value The new CI service in which the test suite was run.
   * @return Job This instance.
   */
  public function setServiceName(string $value): self {
    $this->serviceName = $value;
    return $this;
  }

  /**
   * Gets the build number.
   * @param string $value The new build number.
   * @return Job This instance.
   */
  public function setServiceNumber(string $value): self {
    $this->serviceNumber = $value;
    return $this;
  }

  /**
   * Gets the associated pull request identifier of the build.
   * @param string $value The new pull request identifier.
   * @return Job This instance.
   */
  public function setServicePullRequest(string $value): self {
    $this->servicePullRequest = $value;
    return $this;
  }

  /**
   * Sets the list of source files.
   * @param SourceFile[] $value The new source files.
   * @return Job This instance.
   */
  public function setSourceFiles(array $value): self {
    $this->getSourceFiles()->exchangeArray($value);
    return $this;
  }
}
