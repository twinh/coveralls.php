<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [GitLab CI](https://gitlab.com) configuration parameters from the environment. */
abstract class GitLabCI {

  /**
   * Gets the configuration parameters from the environment.
   * @param array<string, string|null> $environment An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $environment): Configuration {
    return new Configuration([
      'commit_sha' => $environment['CI_BUILD_REF'] ?? null,
      'service_branch' => $environment['CI_BUILD_REF_NAME'] ?? null,
      'service_job_id' => $environment['CI_BUILD_ID'] ?? null,
      'service_job_number' => $environment['CI_BUILD_NAME'] ?? null,
      'service_name' => 'gitlab-ci'
    ]);
  }
}
