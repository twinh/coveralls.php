<?php
declare(strict_types=1);
namespace Coveralls;

use GuzzleHttp\Psr7\{MultipartStream, Request, Response, Uri};
use Lcov\{Record, Report, Token};
use Psr\Http\Message\{UriInterface};
use Rx\{Observable};
use Rx\React\{FromFileObservable, Http};
use Rx\Subject\{Subject};
use Webmozart\PathUtil\{Path};
use function Which\{which};

/**
 * Uploads code coverage reports to the [Coveralls](https://coveralls.io) service.
 */
class Client {

  /**
   * @var string The URL of the default API end point.
   */
  const DEFAULT_ENDPOINT = 'https://coveralls.io';

  /**
   * @var Uri The URL of the API end point.
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
   * @param string|UriInterface $endPoint The URL of the API end point.
   */
  public function __construct($endPoint = self::DEFAULT_ENDPOINT) {
    $this->onRequest = new Subject();
    $this->onResponse = new Subject();
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
   * @return Observable Completes when the operation is done.
   */
  public function upload(string $coverage, Configuration $configuration = null): Observable {
    $coverage = trim($coverage);
    if (!mb_strlen($coverage)) return Observable::error(new \InvalidArgumentException('The specified coverage report is empty.'));

    $report = null;
    $isClover = mb_substr($coverage, 0, 5) == '<?xml' || mb_substr($coverage, 0, 10) == '<coverage';
    if ($isClover) $report = $this->parseCloverReport($coverage);
    else {
      $token = mb_substr($coverage, 0, 3);
      if ($token == Token::TEST_NAME.':' || $token == Token::SOURCE_FILE.':') $report = $this->parseLcovReport($coverage);
    }

    if (!$report) return Observable::error(new \InvalidArgumentException('The specified coverage format is not supported.'));

    $observables = [
      $configuration ? Observable::of($configuration) : Configuration::loadDefaults(),
      which('git')
        ->catch(function() {
          return Observable::of('');
        })
        ->flatMap(function($gitPath) {
          return mb_strlen($gitPath) ? GitData::fromRepository() : Observable::of(null);
        })
    ];

    return $report
      ->zip($observables, function(Job $job, Configuration $config, GitData $git = null) {
        $this->updateJob($job, $config);
        if (!$job->getRunAt()) $job->setRunAt(time());

        if ($git) {
          $branch = ($gitData = $job->getGit()) ? $gitData->getBranch() : '';
          if ($git->getBranch() == 'HEAD' && mb_strlen($branch)) $git->setBranch($branch);
          $job->setGit($git);
        }

        return $job;
      })
      ->flatMap(function($job) {
        return $this->uploadJob($job);
      });
  }

  /**
   * Uploads the specified job to the Coveralls service.
   * @param Job $job The job to be uploaded.
   * @return Observable Completes when the operation is done.
   */
  public function uploadJob(Job $job): Observable {
    if (!$job->getRepoToken() && !$job->getServiceName())
      return Observable::error(new \InvalidArgumentException('The job does not meet the requirements.'));

    $request = new MultipartStream([[
      'contents' => json_encode($job, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
      'filename' => 'coveralls.json',
      'name' => 'json_file'
    ]]);

    $headers = [
      'Content-Length' => $request->getSize(),
      'Content-Type' => "multipart/form-data; boundary={$request->getBoundary()}"
    ];

    $uri = $this->getEndPoint()->withPath('/api/v1/jobs');
    $this->onRequest->onNext(new Request('POST', $uri, $headers, $request));
    return Http::post((string) $uri, $request->getContents(), $headers)->includeResponse()->map(function($data) {
      /** @var \React\HttpClient\Response $response */
      list($body, $response) = $data;
      $this->onResponse->onNext(new Response($response->getCode(), $response->getHeaders(), $body));
      return $body;
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

    $observables = array_map(function(\SimpleXMLElement $file) {
      return isset($file['name']) ?
        new FromFileObservable((string) $file['name']) :
        Observable::error(new \DomainException("Invalid file data: {$file->asXML()}"));
    }, $files);

    $first = array_shift($observables);
    return $first->zip($observables)->map(function($results) use ($files, $workingDir) {
      return new Job(array_map(function($index, $source) use ($files, $workingDir) {
        /** @var \SimpleXMLElement $file */
        $file = $files[$index];

        $lines = preg_split('/\r?\n/', $source);
        $coverage = new \SplFixedArray(count($lines));
        foreach ($file->line as $line) {
          if (!isset($line['type'])) throw new \DomainException("Invalid line data: {$line->asXML()}");
          if ((string) $line['type'] == 'stmt') {
            if (!isset($line['count']) || !isset($line['num'])) throw new \DomainException("Invalid line data: {$line->asXML()}");
            $coverage[(int) $line['num'] - 1] = (int) $line['count'];
          }
        }

        $filename = Path::makeRelative($file['name'], $workingDir);
        return new SourceFile($filename, md5($source), $source, $coverage->toArray());
      }, array_keys($results), $results));
    });
  }

  /**
   * Parses the specified [LCOV](http://ltp.sourceforge.net/coverage/lcov.php) coverage report.
   * @param string $report A coverage report in LCOV format.
   * @return Observable The job corresponding to the specified coverage report.
   */
  private function parseLcovReport(string $report): Observable {
    $records = Report::fromCoverage($report)->getRecords()->getArrayCopy();
    $workingDir = getcwd();

    $observables = array_map(function(Record $record) {
      return new FromFileObservable($record->getSourceFile());
    }, $records);

    $first = array_shift($observables);
    return $first->zip($observables)->map(function($results) use ($records, $workingDir) {
      return new Job(array_map(function($index, $source) use ($records, $workingDir) {
        /** @var Record $record */
        $record = $records[$index];

        $lines = preg_split('/\r?\n/', $source);
        $coverage = new \SplFixedArray(count($lines));
        foreach ($record->getLines()->getData() as $lineData) $coverage[$lineData->getLineNumber() - 1] = $lineData->getExecutionCount();

        $filename = Path::makeRelative($record->getSourceFile(), $workingDir);
        return new SourceFile($filename, md5($source), $source, $coverage->toArray());
      }, array_keys($results), $results));
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
