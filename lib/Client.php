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
  private $endPoint = '';

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
  public function __construct(string $endPoint = '') {
    $this->onRequest = new Subject();
    $this->onResponse = new Subject();
    $this->setEndPoint(mb_strlen($endPoint) ? $endPoint : static::DEFAULT_ENDPOINT);
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
    $coverage = trim($coverage);

    $isClover = mb_substr($coverage, 0, 5) == '<?xml' || mb_substr($coverage, 0, 10) == '<coverage';
    if ($isClover) return $this->uploadJob($this->parseClover($coverage));

    $lcovToken = mb_substr($coverage, 0, 3);
    if ($lcovToken == Token::TEST_NAME.':' || $lcovToken == Token::SOURCE_FILE.':')
      return $this->uploadJob($this->parseLCOV($coverage));

    throw new \InvalidArgumentException('The specified coverage format is not supported.');
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

    $request = (new ServerRequest('POST', $this->getEndPoint()))->withBody(new MultipartStream($jsonFile));
    $this->onRequest->onNext($request);

    $response = (new HTTPClient())->send($request, ['multipart' => $jsonFile]);
    $this->onResponse->onNext($response);

    return $response->getStatusCode() == 200;
  }

  // TODO
  private function parseClover($coverage): Job {

  }

  // TODO
  private function parseLCOV($coverage): Job {

  }
}
