<?php
/**
 * Provides a connector for the [AppVeyor](https://www.appveyor.com) service.
 */
namespace coveralls\services\appveyor;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @param array $env An array providing environment variables.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(array $env): Configuration {
  $repoName = $env['APPVEYOR_REPO_NAME'] ?? null;
  $serviceNumber = $env['APPVEYOR_BUILD_VERSION'] ?? null;

  return new Configuration([
    'commit_sha' => $env['APPVEYOR_REPO_COMMIT'] ?? null,
    'git_author_email' => $env['APPVEYOR_REPO_COMMIT_AUTHOR_EMAIL'] ?? null,
    'git_author_name' => $env['APPVEYOR_REPO_COMMIT_AUTHOR'] ?? null,
    'git_message' => $env['APPVEYOR_REPO_COMMIT_MESSAGE'] ?? null,
    'service_branch' => $env['APPVEYOR_REPO_BRANCH'] ?? null,
    'service_build_url' => $repoName && $serviceNumber ? "https://ci.appveyor.com/project/$repoName/build/$serviceNumber" : null,
    'service_job_id' => $env['APPVEYOR_BUILD_ID'] ?? null,
    'service_job_number' => $env['APPVEYOR_BUILD_NUMBER'] ?? null,
    'service_name' => 'appveyor',
    'service_number' => $serviceNumber
  ]);
}
