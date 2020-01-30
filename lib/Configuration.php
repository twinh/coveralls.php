<?php declare(strict_types=1);
namespace Coveralls;

use Coveralls\Services\{AppVeyor, CircleCI, Codeship, GitHub, GitLabCI, Jenkins, Semaphore, SolanoCI, Surf, TravisCI, Wercker};
use Symfony\Component\Yaml\{Yaml};
use Symfony\Component\Yaml\Exception\{ParseException};

/**
 * Provides access to the coverage settings.
 * @implements \ArrayAccess<string, string|null>
 * @implements \IteratorAggregate<string, string|null>
 */
class Configuration implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable {

  /** @var array<string, string|null> The configuration parameters. */
  private array $params;

  /**
   * Creates a new configuration.
   * @param array<string, string|null> $params The configuration parameters.
   */
  function __construct(array $params = []) {
    $this->params = $params;
  }

  /**
   * Creates a new configuration from the variables of the specified environment.
   * @param array<string, string|null>|null $environment An array providing environment variables. Defaults to `$_SERVER`.
   * @return self The newly created configuration.
   */
  static function fromEnvironment(array $environment = null): self {
    $configuration = new self;
    $environment ??= $_SERVER;

    // Standard.
    $serviceName = $environment['CI_NAME'] ?? '';
    if (mb_strlen($serviceName)) $configuration['service_name'] = $serviceName;

    if (isset($environment['CI_BRANCH'])) $configuration['service_branch'] = $environment['CI_BRANCH'];
    if (isset($environment['CI_BUILD_NUMBER'])) $configuration['service_number'] = $environment['CI_BUILD_NUMBER'];
    if (isset($environment['CI_BUILD_URL'])) $configuration['service_build_url'] = $environment['CI_BUILD_URL'];
    if (isset($environment['CI_COMMIT'])) $configuration['commit_sha'] = $environment['CI_COMMIT'];
    if (isset($environment['CI_JOB_ID'])) $configuration['service_job_id'] = $environment['CI_JOB_ID'];

    if (isset($environment['CI_PULL_REQUEST']) && preg_match('/(\d+)$/', $environment['CI_PULL_REQUEST'], $matches)) {
      if (count($matches) >= 2) $configuration['service_pull_request'] = $matches[1];
    }

    // Coveralls.
    if (isset($environment['COVERALLS_REPO_TOKEN']) || isset($environment['COVERALLS_TOKEN']))
      $configuration['repo_token'] = $environment['COVERALLS_REPO_TOKEN'] ?? $environment['COVERALLS_TOKEN'];

    if (isset($environment['COVERALLS_COMMIT_SHA'])) $configuration['commit_sha'] = $environment['COVERALLS_COMMIT_SHA'];
    if (isset($environment['COVERALLS_FLAG_NAME'])) $configuration['flag_name'] = $environment['COVERALLS_FLAG_NAME'];
    if (isset($environment['COVERALLS_PARALLEL'])) $configuration['parallel'] = $environment['COVERALLS_PARALLEL'];
    if (isset($environment['COVERALLS_RUN_AT'])) $configuration['run_at'] = $environment['COVERALLS_RUN_AT'];
    if (isset($environment['COVERALLS_SERVICE_BRANCH'])) $configuration['service_branch'] = $environment['COVERALLS_SERVICE_BRANCH'];
    if (isset($environment['COVERALLS_SERVICE_JOB_ID'])) $configuration['service_job_id'] = $environment['COVERALLS_SERVICE_JOB_ID'];
    if (isset($environment['COVERALLS_SERVICE_NAME'])) $configuration['service_name'] = $environment['COVERALLS_SERVICE_NAME'];

    // Git.
    if (isset($environment['GIT_AUTHOR_EMAIL'])) $configuration['git_author_email'] = $environment['GIT_AUTHOR_EMAIL'];
    if (isset($environment['GIT_AUTHOR_NAME'])) $configuration['git_author_name'] = $environment['GIT_AUTHOR_NAME'];
    if (isset($environment['GIT_BRANCH'])) $configuration['service_branch'] = $environment['GIT_BRANCH'];
    if (isset($environment['GIT_COMMITTER_EMAIL'])) $configuration['git_committer_email'] = $environment['GIT_COMMITTER_EMAIL'];
    if (isset($environment['GIT_COMMITTER_NAME'])) $configuration['git_committer_name'] = $environment['GIT_COMMITTER_NAME'];
    if (isset($environment['GIT_ID'])) $configuration['commit_sha'] = $environment['GIT_ID'];
    if (isset($environment['GIT_MESSAGE'])) $configuration['git_message'] = $environment['GIT_MESSAGE'];

    // CI services.
    if (isset($environment['TRAVIS'])) {
      $configuration->merge(TravisCI::getConfiguration($environment));
      if (mb_strlen($serviceName) && $serviceName != 'travis-ci') $configuration['service_name'] = $serviceName;
    }
    else if (isset($environment['APPVEYOR'])) $configuration->merge(AppVeyor::getConfiguration($environment));
    else if (isset($environment['CIRCLECI'])) $configuration->merge(CircleCI::getConfiguration($environment));
    else if ($serviceName == 'codeship') $configuration->merge(Codeship::getConfiguration($environment));
    else if (isset($environment['GITHUB_WORKFLOW'])) $configuration->merge(GitHub::getConfiguration($environment));
    else if (isset($environment['GITLAB_CI'])) $configuration->merge(GitLabCI::getConfiguration($environment));
    else if (isset($environment['JENKINS_URL'])) $configuration->merge(Jenkins::getConfiguration($environment));
    else if (isset($environment['SEMAPHORE'])) $configuration->merge(Semaphore::getConfiguration($environment));
    else if (isset($environment['SURF_SHA1'])) $configuration->merge(Surf::getConfiguration($environment));
    else if (isset($environment['TDDIUM'])) $configuration->merge(SolanoCI::getConfiguration($environment));
    else if (isset($environment['WERCKER'])) $configuration->merge(Wercker::getConfiguration($environment));

    return $configuration;
  }

  /**
   * Creates a new configuration from the specified YAML document.
   * @param string $document A YAML document providing configuration parameters.
   * @return self The instance corresponding to the specified YAML document.
   * @throws \InvalidArgumentException The specified document is invalid.
   */
  static function fromYaml(string $document): self {
    assert(mb_strlen($document) > 0);

    try {
      $yaml = Yaml::parse($document);
      if (!is_array($yaml)) throw new \InvalidArgumentException('The specified YAML document is invalid.');
      return new self($yaml);
    }

    catch (ParseException $e) {
      throw new \InvalidArgumentException('The specified YAML document is invalid.', 0, $e);
    }
  }

  /**
   * Loads the default configuration.
   * The default values are read from the environment variables and an optional `.coveralls.yml` file.
   * @param string $coverallsFile The path to the `.coveralls.yml` file. Defaults to the file found in the current directory.
   * @return self The default configuration.
   */
  static function loadDefaults(string $coverallsFile = '.coveralls.yml'): self {
    assert(mb_strlen($coverallsFile) > 0);
    $defaults = static::fromEnvironment();

    try {
      $defaults->merge(static::fromYaml((string) @file_get_contents($coverallsFile)));
      return $defaults;
    }

    catch (\Throwable $e) {
      return $defaults;
    }
  }

  /**
   * Gets the number of entries in this configuration.
   * @return int The number of entries in this configuration.
   */
  function count(): int {
    return count($this->params);
  }

  /**
   * Returns a new iterator that allows iterating the elements of this configuration.
   * @return \Iterator<string, string|null> An iterator for the elements of this configuration.
   */
  function getIterator(): \Iterator {
    return new \ArrayIterator($this->params);
  }

  /**
   * Gets the keys of this configuration.
   * @return string[] The keys of this configuration.
   */
  function getKeys(): array {
    return array_keys($this->params);
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  function jsonSerialize(): \stdClass {
    return (object) $this->params;
  }

  /**
   * Adds all entries of the specified configuration to this one, ignoring `null` values.
   * @param self $configuration The configuration to be merged.
   */
  function merge(self $configuration): void {
    foreach ($configuration as $key => $value)
      if ($value !== null) $this[$key] = $value;
  }

  /**
   * Gets a value indicating whether this configuration contains the specified key.
   * @param string $key The key to seek for.
   * @return bool `true` if this configuration contains the specified key, otherwiser `false`.
   */
  function offsetExists($key): bool {
    assert(is_string($key) && mb_strlen($key) > 0);
    return isset($this->params[$key]);
  }

  /**
   * Gets the value associated to the specified key.
   * @param string $key The key to seek for.
   * @return string The value, or a `null` reference is the key is not found.
   */
  function offsetGet($key): ?string {
    assert(is_string($key) && mb_strlen($key) > 0);
    return $this->params[$key] ?? null;
  }

  /**
   * Associates a given value to the specified key.
   * @param string $key The key to seek for.
   * @param string $value The new value.
   */
  function offsetSet($key, $value): void {
    assert(is_string($key) && mb_strlen($key) > 0);
    $this->params[$key] = $value;
  }

  /**
   * Removes the value associated to the specified key.
   * @param string $key The key to seek for.
   */
  function offsetUnset($key): void {
    assert(is_string($key) && mb_strlen($key) > 0);
    unset($this->params[$key]);
  }
}
