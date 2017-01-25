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
class Configuration implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

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
   * Creates a new configuration from the variables of the specified environment.
   * @param array $env An array providing environment variables. Defaults to `$_ENV` if not empty, otherwise `$_SERVER`.
   * @return Configuration The newly created configuration.
   */
  public static function fromEnvironment(array $env = []): self {
    $config = new static();
    if (!$env) $env = $_ENV ?: $_SERVER;

    // Standard.
    $serviceName = $env['CI_NAME'] ?: '';
    if (mb_strlen($serviceName)) $config['service_name'] = $serviceName;

    if (isset($env['CI_BRANCH'])) $config['service_branch'] = $env['CI_BRANCH'];
    if (isset($env['CI_BUILD_NUMBER'])) $config['service_number'] = $env['CI_BUILD_NUMBER'];
    if (isset($env['CI_BUILD_URL'])) $config['service_build_url'] = $env['CI_BUILD_URL'];
    if (isset($env['CI_COMMIT'])) $config['commit_sha'] = $env['CI_COMMIT'];
    if (isset($env['CI_JOB_ID'])) $config['service_job_id'] = $env['CI_JOB_ID'];

    if (isset($env['CI_PULL_REQUEST']) && preg_match('/(\d+)$/', $env['CI_PULL_REQUEST'], $matches)) {
      if (count($matches) >= 2) $config['service_pull_request'] = $matches[1];
    }

    // Coveralls.
    if (isset($env['COVERALLS_REPO_TOKEN']) || isset($env['COVERALLS_TOKEN']))
      $config['repo_token'] = $env['COVERALLS_REPO_TOKEN'] ?: $env['COVERALLS_TOKEN'];

    if (isset($env['COVERALLS_COMMIT_SHA'])) $config['commit_sha'] = $env['COVERALLS_COMMIT_SHA'];
    if (isset($env['COVERALLS_PARALLEL'])) $config['parallel'] = $env['COVERALLS_PARALLEL'];
    if (isset($env['COVERALLS_RUN_AT'])) $config['run_at'] = $env['COVERALLS_RUN_AT'];
    if (isset($env['COVERALLS_SERVICE_BRANCH'])) $config['service_branch'] = $env['COVERALLS_SERVICE_BRANCH'];
    if (isset($env['COVERALLS_SERVICE_JOB_ID'])) $config['service_job_id'] = $env['COVERALLS_SERVICE_JOB_ID'];
    if (isset($env['COVERALLS_SERVICE_NAME'])) $config['service_name'] = $env['COVERALLS_SERVICE_NAME'];

    // Git.
    if (isset($env['GIT_AUTHOR_EMAIL'])) $config['git_author_email'] = $env['GIT_AUTHOR_EMAIL'];
    if (isset($env['GIT_AUTHOR_NAME'])) $config['git_author_name'] = $env['GIT_AUTHOR_NAME'];
    if (isset($env['GIT_BRANCH'])) $config['service_branch'] = $env['GIT_BRANCH'];
    if (isset($env['GIT_COMMITTER_EMAIL'])) $config['git_committer_email'] = $env['GIT_COMMITTER_EMAIL'];
    if (isset($env['GIT_COMMITTER_NAME'])) $config['git_committer_name'] = $env['GIT_COMMITTER_NAME'];
    if (isset($env['GIT_ID'])) $config['commit_sha'] = $env['GIT_ID'];
    if (isset($env['GIT_MESSAGE'])) $config['git_message'] = $env['GIT_MESSAGE'];

    // CI services.
    $merge = function($service) use ($config) {
      require_once __DIR__."/services/$service.php";
      $config->merge(call_user_func("coveralls\\services\\$service\\getConfiguration"));
    };

    if (isset($env['TRAVIS'])) $merge('travis_ci');
    else if (isset($env['APPVEYOR'])) $merge('appveyor');
    else if (isset($env['CIRCLECI'])) $merge('circleci');
    else if ($serviceName == 'codeship') $merge('codeship');
    else if (isset($env['GITLAB_CI'])) $merge('gitlab_ci');
    else if (isset($env['JENKINS_URL'])) $merge('jenkins');
    else if (isset($env['SEMAPHORE'])) $merge('semaphore');
    else if (isset($env['SURF_SHA1'])) $merge('surf');
    else if (isset($env['TDDIUM'])) $merge('solano_ci');
    else if (isset($env['WERCKER'])) $merge('wercker');

    return $config;
  }

  /**
   * Creates a new configuration from the specified YAML document.
   * @param string $document A YAML document providing configuration parameters.
   * @return Configuration The instance corresponding to the specified YAML document, or `null` if a parsing error occurred.
   */
  public static function fromYAML(string $document) {
    try { return mb_strlen($document) ? new static(Yaml::parse($document)) : null; }
    catch (ParseException $e) { return null; }
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
   * @return \Iterator An iterator for the elements of this configuration.
   */
  public function getIterator(): \Iterator {
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
   * Loads the default configuration.
   * The default values are read from the `.coveralls.yml` file and the environment variables.
   * @param string $coverallsFile The path to an optional `.coveralls.yml` file. Defaults to the file found in the current directory.
   * @return Configuration The default configuration.
   */
  public static function loadDefaults(string $coverallsFile = ''): self {
    if (!mb_strlen($coverallsFile)) $coverallsFile = getcwd().'/.coveralls.yml';

    $defaults = new static();
    if (is_file($coverallsFile)) {
      $config = static::fromYAML(@file_get_contents($coverallsFile));
      if ($config) $defaults->merge($config);
    }

    $defaults->merge(static::fromEnvironment());
    return $defaults;
  }

  /**
   * Adds all key-value pairs of the specified configuration to this one.
   * @param Configuration $config The configuration to be merged.
   */
  public function merge(self $config) {
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
