<?php
/**
 * Implementation of the `coveralls\Job` class.
 */
namespace coveralls;

/**
 * TODO
 */
class Job {

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
    $this->configuration = $configuration ?: clone Configuration::getDefault();
    $this->sourceFiles = new \ArrayObject($sourceFiles);
  }

  /**
   * Gets a value indicating whether the build will not be considered done until a webhook has been sent to Coveralls.
   * @return bool `true` if the build will not be considered done until a webhook has been sent to Coverall, otherwise `false`.
   */
  public function getParallel(): bool {
    return $this->configuration->get('parallel', 'false') == 'true';
  }

  /**
   * Gets the secret token for the repository.
   * @return string The secret token for the repository.
   */
  public function getRepoToken(): string {
    return $this->configuration->get('repo_token', '') ?: $this->configuration->get('repo_secret_token', '');
  }

  /**
   * Gets the CI service or other environment in which the test suite was run.
   * @return string The CI service or other environment in which the test suite was run.
   */
  public function getServiceName(): string {
    return $this->configuration->get('service_name', '');
  }

  /**
   * Gets the list of source files.
   * @return \ArrayObject The source files.
   */
  public function getSourceFiles(): \ArrayObject {
    return $this->sourceFiles;
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
