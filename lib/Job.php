<?php
/**
 * Implementation of the `coveralls\Job` class.
 */
namespace coveralls;

/**
 * Represents the coverage data from a single run of a test suite.
 */
class Job implements \JsonSerializable {

  /**
   * @var Configuration The job configuration.
   */
  private $configuration;

  /**
   * @var \ArrayObject The list of source files.
   */
  private $sourceFiles;

  /**
   * Initializes a new instance of the class.
   * @param array $sourceFiles The list of source files.
   * @param Configuration $configuration The job configuration.
   */
  public function __construct(array $sourceFiles = [], Configuration $configuration = null) {
    $this->configuration = $configuration ?: (new Configuration())->merge(Configuration::getDefault());
    $this->sourceFiles = new \ArrayObject($sourceFiles);
  }

  /**
   * Gets the secret token for the repository.
   * @return string The secret token for the repository.
   */
  public function getRepoToken(): string {
    return $this->configuration['repo_token'] ?: ($this->configuration['repo_secret_token'] ?: '');
  }

  /**
   * Gets the CI service or other environment in which the test suite was run.
   * @return string The CI service or other environment in which the test suite was run.
   */
  public function getServiceName(): string {
    return $this->configuration['service_name'] ?: '';
  }

  /**
   * Gets the list of source files.
   * @return \ArrayObject The source files.
   */
  public function getSourceFiles(): \ArrayObject {
    return $this->sourceFiles;
  }

  /**
   * Gets a value indicating whether the build will not be considered done until a webhook has been sent to Coveralls.
   * @return bool `true` if the build will not be considered done until a webhook has been sent to Coverall, otherwise `false`.
   */
  public function isParallel(): bool {
    $parallel = $this->configuration['parallel'];
    return is_string($parallel) ? mb_strtolower($parallel) == 'true' : (bool) $parallel;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    return (object) [
      // TODO
    ];
  }

  /**
   * Sets a value indicating whether the build will not be considered done until a webhook has been sent to Coveralls.
   * @param bool $value `true` if the build will not be considered done until a webhook has been sent to Coverall, otherwise `false`.
   * @return Job This instance.
   */
  public function setParallel(bool $value) {
    $this->configuration['parallel'] = $value ? 'true' : 'false';
    return $this;
  }

  /**
   * Sets the secret token for the repository.
   * @param string $value The new secret token.
   * @return Job This instance.
   */
  public function setRepoToken(string $value): self {
    $this->configuration['repo_token'] = $value;
    return $this;
  }

  /**
   * Gets the CI service or other environment in which the test suite was run.
   * @param string $value The new CI service in which the test suite was run.
   * @return Job This instance.
   */
  public function setServiceName(string $value): self {
    $this->configuration['service_name'] = $value;
    return $this;
  }

  /**
   * Sets the list of source files.
   * @param SourceFile[] $value The new source files.
   * @return Job This instance.
   */
  public function setSourceFiles(array $value): self {
    $this->getSourceFiles()->exchangeArray($value);
    return $this;
  }
}
