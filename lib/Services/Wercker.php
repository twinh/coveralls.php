<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [Wercker](http://www.wercker.com) configuration parameters from the environment. */
abstract class Wercker {

  /**
   * Gets the configuration parameters from the environment.
   * @param array $env An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $env): Configuration {
    return new Configuration([
      'commit_sha' => $env['WERCKER_GIT_COMMIT'] ?? null,
      'service_branch' => $env['WERCKER_GIT_BRANCH'] ?? null,
      'service_job_id' => $env['WERCKER_BUILD_ID'] ?? null,
      'service_name' => 'wercker'
    ]);
  }
}
