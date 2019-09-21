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
    $repository = $env['GITHUB_REPOSITORY'];

    $gitRef = $env['GITHUB_REF'] ?? '';
    $gitRegex = '#^refs/\w+/#';

    return new Configuration([
      'commit_sha' => $commitSha ?? null,
      'service_branch' => preg_match($gitRegex, $gitRef) ? preg_replace($gitRegex, '', $gitRef) : null,
      'service_build_url' => isset($commitSha) && isset($repository) ? "https://github.com/$repository/commit/$commitSha/checks" : null,
      'service_name' => 'github'
    ]);
  }
}
