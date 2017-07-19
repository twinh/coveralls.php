<?php
declare(strict_types=1);
namespace coveralls;

use GuzzleHttp\{Client as HTTPClient};
use GuzzleHttp\Promise\{PromiseInterface};
use GuzzleHttp\Psr7\{MultipartStream, ServerRequest};
use lcov\{Record, Report, Token};
use Rx\{Observable};
use Rx\Subject\{Subject};
use Webmozart\PathUtil\{Path};
use function which\{which};

/**
 * Uploads code coverage reports to the [Coveralls](https://coveralls.io) service.
 */
class Client {

  /**
   * @var string The URL of the default API end point.
   */
  const DEFAULT_ENDPOINT = 'https://coveralls.io';

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
   * @param Configuration $configuration The environment settings.
   * @return Observable Completes when the operation is done.
   */
  public function upload(string $coverage, Configuration $configuration = null): Observable {
    $coverage = trim($coverage);
    if (!mb_strlen($coverage)) return Observable::error(new \InvalidArgumentException('The specified coverage report is empty.'));

    $parser = null;
    $isClover = mb_substr($coverage, 0, 5) == '<?xml' || mb_substr($coverage, 0, 10) == '<coverage';
    if ($isClover) $parser = $this->parseCloverReport($coverage);
    else {
      $token = mb_substr($coverage, 0, 3);
      if ($token == Token::TEST_NAME.':' || $token == Token::SOURCE_FILE.':') $parser = $this->parseLcovReport($coverage);
    }

    if (!$parser) return Observable::error(new \InvalidArgumentException('The specified coverage format is not supported.'));

    $observables = [
      $parser,
      $configuration ? Observable::of($configuration) : Configuration::loadDefaults(),
      which('git')
        ->catch(function(): Observable {
          return Observable::of('');
        })
        ->flatMap(function(string $gitPath): Observable {
          return mb_strlen($gitPath) ? GitData::fromRepository() : Observable::of(null);
        })
    ];

    return Observable::forkjoin($observables,
      function(Job $job, Configuration $config, GitData $git = null) {
        $this->updateJob($job, $config);
        if (!$job->getRunAt()) $job->setRunAt(time());

        if ($git) {
          $branch = ($gitData = $job->getGit()) ? $gitData->getBranch() : '';
          if ($git->getBranch() == 'HEAD' && mb_strlen($branch)) $git->setBranch($branch);
          $job->setGit($git);
        }

        return $job;
      })
      ->flatMap(function(Job $job): Observable {
        return $this->uploadJob($job);
      });
  }

  /**
   * Uploads the specified job to the Coveralls service.
   * @param Job $job The job to be uploaded.
   * @return Observable Completes when the operation is done.
   * @emits \Psr\Http\Message\RequestInterface The "request" event.
   * @emits \Psr\Http\Message\ResponseInterface The "response" event.
   */
  public function uploadJob(Job $job): Observable {
    if (!$job->getRepoToken() && !$job->getServiceName())
      return Observable::error(new \InvalidArgumentException('The job does not meet the requirements.'));

    $jsonFile = [
      'contents' => json_encode($job, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
      'filename' => 'coveralls.json',
      'name' => 'json_file'
    ];

    $request = (new ServerRequest('POST', "{$this->getEndPoint()}/api/v1/jobs"))->withBody(new MultipartStream([$jsonFile]));
    $promise = (new HTTPClient)->sendAsync($request, [
      'multipart' => [$jsonFile]
    ]);

    $this->onRequest->onNext($request);
    return Observable::of($promise)->map(function(PromiseInterface $promise): string {
      $response = $promise->wait();
      $this->onResponse->onNext($response);
      return (string) $response->getBody();
    });
  }

  /**
   * Parses the specified [Clover](https://www.atlassian.com/software/clover) coverage report.
   * @param string $report A coverage report in LCOV format.
   * @return Observable The job corresponding to the specified coverage report.
   */
  private function parseCloverReport(string $report): Observable {
    $xml = simplexml_load_string($report);
    if (!$xml || !$xml->count() || !$xml->project->count())
      return Observable::error(new \InvalidArgumentException('The specified Clover report is invalid.'));

    $files = array_merge($xml->xpath('/coverage/project/file') ?: [], $xml->xpath('/coverage/project/package/file') ?: []);
    $workingDir = getcwd();

    return Observable::fromArray($files)
      ->map(function(\SimpleXMLElement $file) use ($workingDir): SourceFile {
        $path = (string) $file['name'];
        $data = @file_get_contents($path);
        if (!$data) throw new \RuntimeException("Source file not found: $path");

        $lines = preg_split('/\r?\n/', $data);
        $coverage = new \SplFixedArray(count($lines));
        foreach ($file->line as $line) {
          if ((string) $line['type'] == 'stmt') $coverage[(int) $line['num'] - 1] = (int) $line['count'];
        }

        return new SourceFile(Path::makeRelative($path, $workingDir), md5($data), $data, $coverage->toArray());
      })
      ->toArray()
      ->map(function(array $sourceFiles) use ($xml): Job {
        return (new Job($sourceFiles))->setRunAt((int) $xml->project['timestamp']);
      });
  }

  /**
   * Parses the specified [LCOV](http://ltp.sourceforge.net/coverage/lcov.php) coverage report.
   * @param string $report A coverage report in LCOV format.
   * @return Observable The job corresponding to the specified coverage report.
   */
  private function parseLcovReport(string $report): Observable {
    $records = Report::parse($report)->getRecords()->getArrayCopy();
    $workingDir = getcwd();

    $sourceFiles = array_map(function(Record $record): string {
      return $record->getSourceFile();
    }, $records);

    return Observable::fromArray($sourceFiles)
      ->map(function(string $path) use ($workingDir): SourceFile {
        $data = @file_get_contents($path);
        if (!$data) throw new \RuntimeException("Source file not found: $path");
        return new SourceFile(Path::makeRelative($path, $workingDir), md5($data), $data);
      })
      ->toArray()
      ->map(function(array $sourceFiles) use ($records): Job {
        foreach ($sourceFiles as $index => $sourceFile) {
          /** @var Record $record */
          $record = $records[$index];
          $lines = preg_split('/\r?\n/', $sourceFile->getSource());

          $coverage = new \SplFixedArray(count($lines));
          foreach ($record->getLines()->getData() as $lineData) $coverage[$lineData->getLineNumber() - 1] = $lineData->getExecutionCount();
          $sourceFile->setCoverage($coverage->toArray());
        }

        return new Job($sourceFiles);
      });
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

    $hasGitData = count(array_filter($config->getKeys(), function(string $key): bool {
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
