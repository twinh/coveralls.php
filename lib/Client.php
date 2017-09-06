<?php
declare(strict_types=1);
namespace Coveralls;

use Evenement\{EventEmitterTrait};
use GuzzleHttp\{Client as HTTPClient};
use GuzzleHttp\Psr7\{MultipartStream, Request, Uri};
use Lcov\{Record, Report, Token};
use Psr\Http\Message\{UriInterface};
use Webmozart\PathUtil\{Path};
use function Which\{which};

/**
 * Uploads code coverage reports to the [Coveralls](https://coveralls.io) service.
 */
class Client {
  use EventEmitterTrait;

  /**
   * @var string The URL of the default API end point.
   */
  const DEFAULT_ENDPOINT = 'https://coveralls.io';

  /**
   * @var Uri The URL of the API end point.
   */
  private $endPoint;

  /**
   * Initializes a new instance of the class.
   * @param string|UriInterface $endPoint The URL of the API end point.
   */
  public function __construct($endPoint = self::DEFAULT_ENDPOINT) {
    $this->setEndPoint($endPoint);
  }

  /**
   * Gets the URL of the API end point.
   * @return UriInterface The URL of the API end point.
   */
  public function getEndPoint() {
    return $this->endPoint;
  }

  /**
   * Sets the URL of the API end point.
   * @param string|UriInterface $value The new URL of the API end point.
   * @return Client This instance.
   */
  public function setEndPoint($value): self {
    if ($value instanceof UriInterface) $this->endPoint = $value;
    else if (is_string($value)) $this->endPoint = new Uri($value);
    else $this->endPoint = null;

    return $this;
  }

  /**
   * Uploads the specified code coverage report to the Coveralls service.
   * @param string $coverage A coverage report.
   * @param Configuration $configuration The environment settings.
   * @throws \InvalidArgumentException The specified coverage report is empty or its format is not supported.
   */
  public function upload(string $coverage, Configuration $configuration = null) {
    $coverage = trim($coverage);
    if (!mb_strlen($coverage)) throw new \InvalidArgumentException('The specified coverage report is empty.');

    $job = null;
    $isClover = mb_substr($coverage, 0, 5) == '<?xml' || mb_substr($coverage, 0, 10) == '<coverage';
    if ($isClover) $job = $this->parseCloverReport($coverage);
    else {
      $token = mb_substr($coverage, 0, 3);
      if ($token == Token::TEST_NAME.':' || $token == Token::SOURCE_FILE.':') $job = $this->parseLcovReport($coverage);
    }

    if (!$job) throw new \InvalidArgumentException('The specified coverage format is not supported.');
    $this->updateJob($job, $configuration ?: Configuration::loadDefaults());
    if (!$job->getRunAt()) $job->setRunAt(time());

    try {
      if (which('git')) {
        $git = GitData::fromRepository();
        $branch = ($gitData = $job->getGit()) ? $gitData->getBranch() : '';
        if ($git->getBranch() == 'HEAD' && mb_strlen($branch)) $git->setBranch($branch);
        $job->setGit($git);
      }
    }

    catch (\RuntimeException $e) {}
    $this->uploadJob($job);
  }

  /**
   * Uploads the specified job to the Coveralls service.
   * @param Job $job The job to be uploaded.
   * @throws \InvalidArgumentException The job does not meet the requirements.
   * @throws \RuntimeException An error occurred while uploading the report.
   */
  public function uploadJob(Job $job) {
    if (!$job->getRepoToken() && !$job->getServiceName())
      throw new \InvalidArgumentException('The job does not meet the requirements.');

    try {
      $body = new MultipartStream([[
        'contents' => json_encode($job, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        'filename' => 'coveralls.json',
        'name' => 'json_file'
      ]]);

      $headers = [
        'Content-Length' => $body->getSize(),
        'Content-Type' => "multipart/form-data; boundary={$body->getBoundary()}"
      ];

      $request = new Request('POST', $this->getEndPoint()->withPath('/api/v1/jobs'), $headers, $body);
      $this->emit('request', [$request]);

      $response = (new HTTPClient())->send($request);
      $this->emit('reponse', [$response]);
    }

    catch (\Throwable $e) {
      throw new \RuntimeException('An error occurred while uploading the report.', 0, $e);
    }
  }

  /**
   * Parses the specified [Clover](https://www.atlassian.com/software/clover) coverage report.
   * @param string $report A coverage report in LCOV format.
   * @return Job The job corresponding to the specified coverage report.
   * @throws \InvalidArgumentException The specified Clover report has an invalid format.
   * @throws \RuntimeException A source file was not found.
   */
  private function parseCloverReport(string $report): Job {
    $xml = @simplexml_load_string($report);
    if (!$xml || !$xml->count() || !$xml->project->count())
      throw new \InvalidArgumentException('The specified Clover report is invalid.');

    $files = array_merge($xml->xpath('/coverage/project/file') ?: [], $xml->xpath('/coverage/project/package/file') ?: []);
    $workingDir = getcwd();

    return new Job(array_map(function(\SimpleXMLElement $file) use ($workingDir) {
      if (!isset($file['name'])) throw new \InvalidArgumentException("Invalid file data: {$file->asXML()}");

      $path = (string) $file['name'];
      $source = (string) @file_get_contents($path);
      if (!mb_strlen($source)) throw new \RuntimeException("Source file not found: $path");

      $lines = preg_split('/\r?\n/', $source);
      $coverage = new \SplFixedArray(count($lines));
      foreach ($file->line as $line) {
        if (!isset($line['type'])) throw new \InvalidArgumentException("Invalid line data: {$line->asXML()}");
        if ((string) $line['type'] == 'stmt') {
          if (!isset($line['count']) || !isset($line['num'])) throw new \InvalidArgumentException("Invalid line data: {$line->asXML()}");
          $coverage[(int) $line['num'] - 1] = (int) $line['count'];
        }
      }

      $filename = Path::makeRelative((string) $file['name'], $workingDir);
      return new SourceFile($filename, md5($source), $source, $coverage->toArray());
    }, $files));
  }

  /**
   * Parses the specified [LCOV](http://ltp.sourceforge.net/coverage/lcov.php) coverage report.
   * @param string $report A coverage report in LCOV format.
   * @return Job The job corresponding to the specified coverage report.
   * @throws \RuntimeException A source file was not found.
   */
  private function parseLcovReport(string $report): Job {
    $records = Report::fromCoverage($report)->getRecords()->getArrayCopy();
    $workingDir = getcwd();

    return new Job(array_map(function(Record $record) use ($workingDir) {
      $path = $record->getSourceFile();
      $source = (string) @file_get_contents($path);
      if (!mb_strlen($source)) throw new \RuntimeException("Source file not found: $path");

      $lines = preg_split('/\r?\n/', $source);
      $coverage = new \SplFixedArray(count($lines));
      foreach ($record->getLines()->getData() as $lineData) $coverage[$lineData->getLineNumber() - 1] = $lineData->getExecutionCount();

      $filename = Path::makeRelative($path, $workingDir);
      return new SourceFile($filename, md5($source), $source, $coverage->toArray());
    }, $records));
  }

  /**
   * Updates the properties of the specified job using the given configuration parameters.
   * @param Job $job The job to update.
   * @param Configuration $config The parameters to define.
   */
  private function updateJob(Job $job, Configuration $config) {
    if (isset($config['repo_token']) || isset($config['repo_secret_token']))
      $job->setRepoToken($config['repo_token'] ?? $config['repo_secret_token']);

    if (isset($config['parallel'])) $job->setParallel($config['parallel'] == 'true');
    if (isset($config['run_at'])) $job->setRunAt($config['run_at']);
    if (isset($config['service_job_id'])) $job->setServiceJobId($config['service_job_id']);
    if (isset($config['service_name'])) $job->setServiceName($config['service_name']);
    if (isset($config['service_number'])) $job->setServiceNumber($config['service_number']);
    if (isset($config['service_pull_request'])) $job->setServicePullRequest($config['service_pull_request']);

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
  }
}
