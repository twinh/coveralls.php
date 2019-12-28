<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [Wercker](https://app.wercker.com) configuration parameters from the environment. */
abstract class Wercker {

  /**
   * Gets the configuration parameters from the environment.
   * @param array<string, string|null> $environment An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $environment): Configuration {
    return new Configuration([
      'commit_sha' => $environment['WERCKER_GIT_COMMIT'] ?? null,
      'service_branch' => $environment['WERCKER_GIT_BRANCH'] ?? null,
      'service_job_id' => $environment['WERCKER_BUILD_ID'] ?? null,
      'service_name' => 'wercker'
    ]);
  }
}
