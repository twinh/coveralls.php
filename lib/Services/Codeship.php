<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [Codeship](https://circleci.com) configuration parameters from the environment. */
abstract class Codeship {

  /**
   * Gets the configuration parameters from the environment.
   * @param array<string, string|null> $environment An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $environment): Configuration {
    return new Configuration([
      'commit_sha' => $environment['CI_COMMIT_ID'] ?? null,
      'git_committer_email' => $environment['CI_COMMITTER_EMAIL'] ?? null,
      'git_committer_name' => $environment['CI_COMMITTER_NAME'] ?? null,
      'git_message' => $environment['CI_COMMIT_MESSAGE'] ?? null,
      'service_job_id' => $environment['CI_BUILD_NUMBER'] ?? null,
      'service_name' => 'codeship'
    ]);
  }
}
