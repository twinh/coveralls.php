<?php
/**
 * Implementation of the `coveralls\Configuration` class.
 */
namespace coveralls;

use coveralls\services\{appveyor, circleci, codeship, gitlab_ci, jenkins, surf, travis_ci, wercker};
use Symfony\Component\Yaml\Yaml;

/**
 * Provides access to the coverage settings.
 */
class Configuration implements \ArrayAccess, \Countable, \IteratorAggregate {

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

    if ($value = getenv('COVERALLS_GIT_BRANCH')) $config['git_branch'] = $value;
    if ($value = getenv('COVERALLS_GIT_COMMIT')) $config['git_commit'] = $value;
    if (getenv('COVERALLS_PARALLEL') !== false) $config['parallel'] = true;
    if ($value = getenv('COVERALLS_REPO_TOKEN')) $config['repo_token'] = $value;
    $config['run_at'] = getenv('COVERALLS_RUN_AT') ?: (new \DateTime())->format('c');
    if ($value = getenv('COVERALLS_SERVICE_JOB_ID')) $config['service_job_id'] = $value;
    if ($value = getenv('COVERALLS_SERVICE_NAME')) $config['service_name'] = $value;

    /*
    $matches = new RegExp(r'(\d+)$').allMatches(getenv('CI_PULL_REQUEST') ?: '');
    if ($matches.length >= 2) $params['service_pull_request'] = $matches[1];
    */

    $fetch = function($service) use ($config) {
      require_once __DIR__."/services/$service.php";
      foreach (call_user_func("coveralls\\services\\$service\\getConfiguration") as $key => $value) $config[$key] = $value;
    };

    if (getenv('TRAVIS') !== false) $fetch('travis_ci');
    else if (getenv('APPVEYOR') !== false) $fetch('appveyor');
    else if (getenv('CIRCLECI') !== false) $fetch('circleci');
    else if (getenv('CI_NAME') == 'codeship') $fetch('codeship');
    else if (getenv('GITLAB_CI') !== false) $fetch('gitlab_ci');
    else if (getenv('JENKINS_URL') !== false) $fetch('jenkins');
    else if (getenv('SURF_SHA1') !== false) $fetch('surf');
    else if (getenv('WERCKER') !== false) $fetch('wercker');

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
   * Gets the number of key-value pairs in this configuration.
   * @return int The number of key-value pairs in this configuration.
   */
  public function count(): int {
    return count($this->params);
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
