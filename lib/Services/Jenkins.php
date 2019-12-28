<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [Jenkins](https://jenkins.io) configuration parameters from the environment. */
abstract class Jenkins {

  /**
   * Gets the configuration parameters from the environment.
   * @param array<string, string|null> $environment An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $environment): Configuration {
    return new Configuration([
      'commit_sha' => $environment['GIT_COMMIT'] ?? null,
      'service_branch' => $environment['GIT_BRANCH'] ?? null,
      'service_build_url' => $environment['BUILD_URL'] ?? null,
      'service_job_id' => $environment['BUILD_ID'] ?? null,
      'service_name' => 'jenkins',
      'service_number' => $environment['BUILD_NUMBER'] ?? null,
      'service_pull_request' => $environment['ghprbPullId'] ?? null
    ]);
  }
}
