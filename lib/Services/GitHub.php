<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [GitHub](https://github.com) configuration parameters from the environment. */
abstract class GitHub {

  /**
   * Gets the configuration parameters from the environment.
   * @param array $env An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $env): Configuration {
    $commitSha = $env['GITHUB_SHA'];
    $gitRef = $env['GITHUB_REF'] ?? '';
    $repository = $env['GITHUB_REPOSITORY'];

    return new Configuration([
      'commit_sha' => $commitSha ?? null,
      'service_branch' => mb_substr($gitRef, 0, 11) == 'refs/heads/' ? mb_substr($gitRef, 11) : null,
      'service_build_url' => isset($commitSha) && isset($repository) ? "https://github.com/$repository/commit/$commitSha/checks" : null,
      'service_name' => 'github'
    ]);
  }
}
