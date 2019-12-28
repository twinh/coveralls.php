<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [CircleCI](https://circleci.com) configuration parameters from the environment. */
abstract class CircleCI {

  /**
   * Gets the configuration parameters from the environment.
   * @param array<string, string|null> $environment An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $environment): Configuration {
    $nodes = (int) ($environment['CIRCLE_NODE_TOTAL'] ?? '0');
    return new Configuration([
      'commit_sha' => $environment['CIRCLE_SHA1'] ?? null,
      'parallel' => $nodes > 1 ? 'true' : 'false',
      'service_branch' => $environment['CIRCLE_BRANCH'] ?? null,
      'service_build_url' => $environment['CIRCLE_BUILD_URL'] ?? null,
      'service_job_number' => $environment['CIRCLE_NODE_INDEX'] ?? null,
      'service_name' => 'circleci',
      'service_number' => $environment['CIRCLE_BUILD_NUM'] ?? null
    ]);
  }
}
