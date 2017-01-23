<?php
/**
 * Implementation of the `coveralls\Configuration` class.
 */
namespace coveralls;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides access to the coverage settings.
 */
class Configuration implements \ArrayAccess, \IteratorAggregate {

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
   * Creates a new configuration from the environment variables.
   * @return Configuration The newly created configuration.
   */
  public static function fromEnvironment(): self {
    $config = new static();

    // TODO: not sure if a default value is required.
    $config['parallel'] = getenv('COVERALLS_PARALLEL') == 'true';
    $config['run_at'] = getenv('COVERALLS_RUN_AT') ?: (new \DateTime())->format('c');

    if ($value = getenv('COVERALLS_GIT_BRANCH')) $config['git_branch'] = $value;
    if ($value = getenv('COVERALLS_GIT_COMMIT')) $config['git_commit'] = $value;
    if ($value = getenv('COVERALLS_REPO_TOKEN')) $config['repo_token'] = $value;
    if ($value = getenv('COVERALLS_SERVICE_JOB_ID')) $config['service_job_id'] = $value;
    if ($value = getenv('COVERALLS_SERVICE_NAME')) $config['service_name'] = $value;

    /*
    $matches = new RegExp(r'(\d+)$').allMatches(getenv('CI_PULL_REQUEST') ?: '');
    if ($matches.length >= 2) $params['service_pull_request'] = $matches[1];
    */

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
    else if (getenv('SURF_SHA1') !== false) $merge('surf');
    else if (getenv('WERCKER') !== false) $merge('wercker');

    return $config;
  }

  /**
   * Creates a new configuration from the specified YAML document.
   * @param string $document A YAML document providing configuration parameters.
   * @return Configuration The newly created configuration.
   */
  public static function fromYAML(string $document): self {
    return new static(Yaml::parse($document));
  }

  /**
   * Returns the default configuration.
   * @return Configuration The default configuration.
   */
  public static function getDefault(): self {
    static $instance;

    if (!isset($instance)) {
      $instance = new static();
      if (is_file($path = getcwd().'/.coveralls.yml')) $instance->merge(static::fromYAML(@file_get_contents($path)));
      $instance->merge(static::fromEnvironment());
    }

    return $instance;
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
  public function merge(self $config) {
    foreach ($config as $key => $value) $this->offsetSet($key, $value);
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
