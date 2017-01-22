<?php
/**
 * Implementation of the `coveralls\Configuration` class.
 */
namespace coveralls;

use coveralls\services\{
  appveyor,
  circleci,
  codeship,
  gitlab_ci,
  jenkins,
  surf,
  travis_ci,
  wercker
};

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
    $params = ['run_at' => getenv('COVERALLS_RUN_AT') ?: (new \DateTime())->format('c')];

    if ($value = getenv('COVERALLS_GIT_BRANCH')) $params['git_branch'] = $value;
    if ($value = getenv('COVERALLS_GIT_COMMIT')) $params['git_commit'] = $value;
    if (getenv('COVERALLS_PARALLEL') !== false) $params['parallel'] = true;
    if ($value = getenv('COVERALLS_REPO_TOKEN')) $params['repo_token'] = $value;
    if ($value = getenv('COVERALLS_SERVICE_JOB_ID')) $params['service_job_id'] = $value;
    if ($value = getenv('COVERALLS_SERVICE_NAME')) $params['service_name'] = $value;

    /*
    $matches = new RegExp(r'(\d+)$').allMatches(getenv('CI_PULL_REQUEST') ?: '');
    if ($matches.length >= 2) $params['service_pull_request'] = $matches[1];
    */

    $assign = function(array $values) use ($params) {
      foreach ($values as $key => $value) { $params[$key] = $value; }
    };

    if (getenv('TRAVIS') !== false) $assign(travis_ci\getConfiguration());
    else if (getenv('APPVEYOR') !== false) $assign(appveyor\getConfiguration());
    else if (getenv('CIRCLECI') !== false) $assign(circleci\getConfiguration());
    else if (getenv('CI_NAME') == 'codeship') $assign(codeship\getConfiguration());
    else if (getenv('GITLAB_CI') !== false) $assign(gitlab_ci\getConfiguration());
    else if (getenv('JENKINS_URL') !== false) $assign(jenkins\getConfiguration());
    else if (getenv('SURF_SHA1') !== false) $assign(surf\getConfiguration());
    else if (getenv('WERCKER') !== false) $assign(wercker\getConfiguration());

    return new static($params);
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
   * Gets the number of key-value pairs in the map.
   * @return int The number of key-value pairs in the map.
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
    return array_key_exists($offset, $this->params);
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
