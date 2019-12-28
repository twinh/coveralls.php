<?php declare(strict_types=1);
namespace Coveralls\Services;

use Coveralls\{Configuration};

/** Fetches the [AppVeyor](https://www.appveyor.com) configuration parameters from the environment. */
abstract class AppVeyor {

  /**
   * Gets the configuration parameters from the environment.
   * @param array<string, string|null> $environment An array providing environment variables.
   * @return Configuration The configuration parameters.
   */
  static function getConfiguration(array $environment): Configuration {
    $repoName = $environment['APPVEYOR_REPO_NAME'] ?? null;
    $serviceNumber = $environment['APPVEYOR_BUILD_VERSION'] ?? null;

    return new Configuration([
      'commit_sha' => $environment['APPVEYOR_REPO_COMMIT'] ?? null,
      'git_author_email' => $environment['APPVEYOR_REPO_COMMIT_AUTHOR_EMAIL'] ?? null,
      'git_author_name' => $environment['APPVEYOR_REPO_COMMIT_AUTHOR'] ?? null,
      'git_message' => $environment['APPVEYOR_REPO_COMMIT_MESSAGE'] ?? null,
      'service_branch' => $environment['APPVEYOR_REPO_BRANCH'] ?? null,
      'service_build_url' => $repoName && $serviceNumber ? "https://ci.appveyor.com/project/$repoName/build/$serviceNumber" : null,
      'service_job_id' => $environment['APPVEYOR_BUILD_ID'] ?? null,
      'service_job_number' => $environment['APPVEYOR_BUILD_NUMBER'] ?? null,
      'service_name' => 'appveyor',
      'service_number' => $serviceNumber
    ]);
  }
}
