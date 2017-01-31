<?php
/**
 * Implementation of the `coveralls\Client` class.
 */
namespace coveralls;

use GuzzleHttp\{Client as HTTPClient};
use GuzzleHttp\Psr7\{MultipartStream, ServerRequest};
use lcov\{Report, Token};
use Rx\{Observable};
use Rx\Subject\{Subject};

/**
 * Uploads code coverage reports to the [Coveralls](https://coveralls.io) service.
 */
class Client {

  /**
   * @var string The URL of the default end point.
   */
  const DEFAULT_ENDPOINT = 'https://coveralls.io/api/v1/jobs';

  /**
   * @var string The URL of the API end point.
   */
  private $endPoint;

  /**
   * @var Subject The handler of "request" events.
   */
  private $onRequest;

  /**
   * @var Subject The handler of "response" events.
   */
  private $onResponse;

  /**
   * Initializes a new instance of the class.
   * @param string $endPoint The URL of the API end point.
   */
  public function __construct(string $endPoint = self::DEFAULT_ENDPOINT) {
    $this->onRequest = new Subject();
    $this->onResponse = new Subject();
    $this->setEndPoint($endPoint);
  }

  /**
   * Gets the URL of the API end point.
   * @return string The URL of the API end point.
   */
  public function getEndPoint(): string {
    return $this->endPoint;
  }

  /**
   * Gets the stream of "request" events.
   * @return Observable The stream of "request" events.
   */
  public function onRequest(): Observable {
    return $this->onRequest->asObservable();
  }

  /**
   * Gets the stream of "response" events.
   * @return Observable The stream of "response" events.
   */
  public function onResponse(): Observable {
    return $this->onResponse->asObservable();
  }

  /**
   * Sets the URL of the API end point.
   * @param string $value The new URL of the API end point.
   * @return Client This instance.
   */
  public function setEndPoint(string $value) {
    $this->endPoint = $value;
    return $this;
  }

  /**
   * Uploads the specified code coverage report to the Coveralls service.
   * @param string $coverage A coverage report.
   * @param Configuration $config The environment settings.
   * @return bool `true` if the operation succeeds, otherwise `false`.
   * @throws \InvalidArgumentException The specified coverage format is not supported.
   */
  public function upload(string $coverage, Configuration $config = null): bool {
    // Parse the coverage.
    $coverage = trim($coverage);
    $job = null;

    $isClover = mb_substr($coverage, 0, 5) == '<?xml' || mb_substr($coverage, 0, 10) == '<coverage';
    if ($isClover) $job = $this->parseCloverReport($coverage);
    else {
      $token = mb_substr($coverage, 0, 3);
      if ($token == Token::TEST_NAME.':' || $token == Token::SOURCE_FILE.':') $job = $this->parseLcovReport($coverage);
    }

    if (!$job) throw new \InvalidArgumentException('The specified coverage format is not supported.');

    // Apply the configuration settings.
    if (!$config) $config = Configuration::loadDefaults();

    $hasGitData = count(array_filter($config->getKeys(), function($key) {
      return $key == 'service_branch' || mb_substr($key, 0, 4) == 'git_';
    })) > 0;

    if (!$hasGitData) $job->setCommitSha($config['commit_sha'] ?: '');
    else {
      $commit = new GitCommit($config['commit_sha'] ?: '', $config['git_message'] ?: '');
      $commit->setAuthorEmail($config['git_author_email'] ?: '');
      $commit->setAuthorName($config['git_author_name'] ?: '');
      $commit->setCommitterEmail($config['git_committer_email'] ?: '');
      $commit->setCommitterName($config['git_committer_email'] ?: '');

      $job->setGit(new GitData($commit, $config['service_branch'] ?: ''));
    }

    $job->setParallel($config['parallel'] == 'true');
    $job->setRepoToken($config['repo_token'] ?: ($config['repo_secret_token'] ?: ''));
    $job->setRunAt($config['run_at'] ? new \DateTime($config['run_at']) : null);
    $job->setServiceJobId($config['service_job_id'] ?: '');
    $job->setServiceName($config['service_name'] ?: '');
    $job->setServiceNumber($config['service_number'] ?: '');
    $job->setServicePullRequest($config['service_pull_request'] ?: '');

    return $this->uploadJob($job);
  }

  /**
   * Uploads the specified job to the Coveralls service.
   * @param Job $job The job to be uploaded.
   * @return bool `true` if the operation succeeds, otherwise `false`.
   */
  public function uploadJob(Job $job): bool {
    $jsonFile = [
      'contents' => json_encode($job, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
      'name' => 'json_file'
    ];

    $request = (new ServerRequest('POST', $this->getEndPoint()))->withBody(new MultipartStream([$jsonFile]));
    $this->onRequest->onNext($request);

    $response = (new HTTPClient())->send($request, ['multipart' => [$jsonFile]]);
    $this->onResponse->onNext($response);

    return $response->getStatusCode() == 200;
  }

  /**
   * Parses the specified [Clover](https://www.atlassian.com/software/clover) coverage report.
   * @param string $coverage A coverage report in LCOV format.
   * @return Job The job corresponding to the specified coverage report.
   */
  private function parseCloverReport(string $coverage): Job {
    $sourceFiles = [];
    // TODO
    return new Job($sourceFiles);
  }

  /**
   * Parses the specified [LCOV](http://ltp.sourceforge.net/coverage/lcov.php) coverage report.
   * @param string $coverage A coverage report in LCOV format.
   * @return Job The job corresponding to the specified coverage report.
   */
  private function parseLcovReport(string $coverage): Job {
    $records = Report::parse($coverage)->getRecords()->getArrayCopy();
    return new Job(array_map(function($record) {
      // TODO
    }, $records));
  }
}
