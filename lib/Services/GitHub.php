<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [GitHub](https://github.com) configuration parameters from the environment. */
abstract class GitHub {

  /**
   * Gets the configuration parameters from the environment.
   * @param array<string, string|null> $environment An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $environment): Configuration {
    $commitSha = $environment['GITHUB_SHA'] ?? '';
    $repository = $environment['GITHUB_REPOSITORY'] ?? '';

    $gitRef = $environment['GITHUB_REF'] ?? '';
    $gitRegex = '#^refs/\w+/#';

    return new Configuration([
      'commit_sha' => $commitSha ?? null,
      'service_branch' => preg_match($gitRegex, $gitRef) ? preg_replace($gitRegex, '', $gitRef) : null,
      'service_build_url' => $commitSha && $repository ? "https://github.com/$repository/commit/$commitSha/checks" : null,
      'service_name' => 'github'
    ]);
  }
}
