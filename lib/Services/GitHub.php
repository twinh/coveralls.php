<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [GitHub](https://github.com) configuration parameters from the environment. */
abstract class TravisCI {

  /**
   * Gets the configuration parameters from the environment.
   * @param array $env An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $env): Configuration {
    return new Configuration([
      'commit_sha' => $env['GITHUB_SHA'] ?? null,
      'service_branch' => $env['GITHUB_REF'] ?? null,
      'service_name' => 'github'
    ]);
  }
}
