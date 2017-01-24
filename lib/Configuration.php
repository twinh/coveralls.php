<?php
/**
 * Implementation of the `coveralls\Configuration` class.
 */
namespace coveralls;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Provides access to the coverage settings.
 */
class Configuration implements \ArrayAccess, \IteratorAggregate, \JsonSerializable {

  /**
   * @var Configuration The default configuration.
   */
  private static $default;

  /**
   * @var array The configuration parameters.
   */
  private $params;

  /**
   * Initializes a new instance of the class.
   * @param array $params The configuration parameters.
   */
  public function __construct(array $params = []) {
    $this->params = $params;
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
   * Creates a new configuration from the environment variables.
   * @return Configuration The newly created configuration.
   */
  public static function fromEnvironment(): self {
    $config = new static();

    // Standard.
    if ($value = getenv('CI_BRANCH')) $config['service_branch'] = $value;
    if ($value = getenv('CI_BUILD_NUMBER')) $config['service_number'] = $value;
    if ($value = getenv('CI_BUILD_URL')) $config['service_build_url'] = $value;
    if ($value = getenv('CI_COMMIT')) $config['commit_sha'] = $value;
    if ($value = getenv('CI_JOB_ID')) $config['service_job_id'] = $value;
    if ($value = getenv('CI_NAME')) $config['service_name'] = $value;

    /* TODO
    if ($value = getenv('CI_PULL_REQUEST')) {
      preg_match_all('/(\d+)$/', $value, $matches);
      if (count($matches) >= 2) $config['service_pull_request'] = $matches[1];
    }*/

    // Coveralls.
    if ($value = getenv('COVERALLS_COMMIT_SHA')) $config['commit_sha'] = $value;
    if ($value = getenv('COVERALLS_PARALLEL')) $config['parallel'] = $value;
    if ($value = getenv('COVERALLS_REPO_TOKEN')) $config['repo_token'] = $value;
    if ($value = getenv('COVERALLS_RUN_AT')) $config['run_at'] = $value;
    if ($value = getenv('COVERALLS_SERVICE_BRANCH')) $config['service_branch'] = $value;
    if ($value = getenv('COVERALLS_SERVICE_JOB_ID')) $config['service_job_id'] = $value;
    if ($value = getenv('COVERALLS_SERVICE_NAME')) $config['service_name'] = $value;

    // Git.
    if ($value = getenv('GIT_AUTHOR_EMAIL')) $config['git_author_email'] = $value;
    if ($value = getenv('GIT_AUTHOR_NAME')) $config['git_author_name'] = $value;
    if ($value = getenv('GIT_BRANCH')) $config['service_branch'] = $value;
    if ($value = getenv('GIT_COMMITTER_EMAIL')) $config['git_committer_email'] = $value;
    if ($value = getenv('GIT_COMMITTER_NAME')) $config['git_committer_name'] = $value;
    if ($value = getenv('GIT_ID')) $config['commit_sha'] = $value;
    if ($value = getenv('GIT_MESSAGE')) $config['git_message'] = $value;

    // CI services.
    $merge = function($service) use ($config) {
      require_once __DIR__."/services/$service.php";
      $config->merge(call_user_func("coveralls\\services\\$service\\getConfiguration"));
    };

    if (getenv('TRAVIS') !== false) $merge('travis_ci');
    else if (getenv('APPVEYOR') !== false) $merge('appveyor');
    else if (getenv('CIRCLECI') !== false) $merge('circleci');
    else if (getenv('CI_NAME') == 'codeship') $merge('codeship');
    else if (getenv('GITLAB_CI') !== false) $merge('gitlab_ci');
    else if (getenv('JENKINS_URL') !== false) $merge('jenkins');
    else if (getenv('SEMAPHORE') !== false) $merge('semaphore');
    else if (getenv('SURF_SHA1') !== false) $merge('surf');
    else if (getenv('TDDIUM') !== false) $merge('solano_ci');
    else if (getenv('WERCKER') !== false) $merge('wercker');

    return $config;
  }

  /**
   * Creates a new configuration from the specified JSON map.
   * @param mixed $map A JSON map representing configuration parameters.
   * @return Configuration The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJSON($map) {
    if (is_object($map)) $map = (array) $map;
    return is_array($map) ? new static($map) : null;
  }

  /**
   * Creates a new configuration from the specified YAML document.
   * @param string $document A YAML document providing configuration parameters.
   * @return Configuration The instance corresponding to the specified YAML document, or `null` if a parsing error occurred.
   */
  public static function fromYAML(string $document) {
    try { return mb_strlen($document) ? static::fromJSON(Yaml::parse($document)) : null; }
    catch (ParseException $e) { return null; }
  }

  /**
   * Returns the default configuration.
   * @return Configuration The default configuration.
   */
  public static function getDefault(): self {
    if (!static::$default) {
      static::$default = new static();

      if (is_file($path = getcwd().'/.coveralls.yml')) {
        $config = static::fromYAML(@file_get_contents($path));
        if ($config) static::$default->merge($config);
      }

      static::$default->merge(static::fromEnvironment());
    }

    return static::$default;
  }

  /**
   * Returns a new iterator that allows iterating the elements of this configuration.
   * @return \Traversable An iterator for the elements of this configuration.
   */
  public function getIterator(): \Traversable {
    return new \ArrayIterator($this->params);
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    return (object) $this->params;
  }

  /**
   * Adds all key-value pairs of the specified configuration to this one.
   * @param Configuration $config The configuration to be merged.
   */
  public function merge(self $config): self {
    foreach ($config as $key => $value) $this[$key] = $value;
  }

  /**
   * Gets a value indicating whether this configuration contains the specified offset.
   * @param string $offset The offset to seek for.
   * @return bool `true` if this configuration contains the specified offset, otherwiser `false`.
   */
  public function offsetExists($offset): bool {
    return isset($this->params[$offset]);
  }

  /**
   * Gets the value located at the specified offset.
   * @param string $offset The offset to seek for.
   * @return mixed The value, or a `null` reference is the offset is not found.
   */
  public function offsetGet($offset) {
    return $this->params[$offset] ?? null;
  }

  /**
   * Associates an offset with the given value.
   * @param string $offset The offset to seek for.
   * @param mixed $value The new value.
   */
  public function offsetSet($offset, $value) {
    $this->params[$offset] = $value;
  }

  /**
   * Removes the value located at the specified offset.
   * @param string $offset The offset to seek for.
   */
  public function offsetUnset($offset) {
    unset($this->params[$offset]);
  }
}
