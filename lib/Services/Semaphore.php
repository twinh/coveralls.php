<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [Semaphore](https://semaphoreci.com) configuration parameters from the environment. */
abstract class Semaphore {

  /**
   * Gets the configuration parameters from the environment.
   * @param array<string, string|null> $environment An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $environment): Configuration {
    return new Configuration([
      'commit_sha' => $environment['REVISION'] ?? null,
      'service_branch' => $environment['BRANCH_NAME'] ?? null,
      'service_name' => 'semaphore',
      'service_number' => $environment['SEMAPHORE_BUILD_NUMBER'] ?? null,
      'service_pull_request' => $environment['PULL_REQUEST_NUMBER'] ?? null
    ]);
  }
}
