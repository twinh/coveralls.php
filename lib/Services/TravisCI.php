<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [Travis CI](https://travis-ci.com) configuration parameters from the environment. */
abstract class TravisCI {

  /**
   * Gets the configuration parameters from the environment.
   * @param array<string, string|null> $environment An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $environment): Configuration {
    $configuration = new Configuration([
      'commit_sha' => $environment['TRAVIS_COMMIT'] ?? null,
      'flag_name' => $environment['TRAVIS_JOB_NAME'] ?? null,
      'git_message' => $environment['TRAVIS_COMMIT_MESSAGE'] ?? null,
      'service_branch' => $environment['TRAVIS_BRANCH'] ?? null,
      'service_build_url' => $environment['TRAVIS_BUILD_WEB_URL'] ?? null,
      'service_job_id' => $environment['TRAVIS_JOB_ID'] ?? null,
      'service_name' => 'travis-ci'
    ]);

    $pullRequest = $environment['TRAVIS_PULL_REQUEST'] ?? null;
    if ($pullRequest && $pullRequest != 'false') $configuration['service_pull_request'] = $pullRequest;

    return $configuration;
  }
}
