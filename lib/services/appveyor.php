<?php
/**
 * Provides a connector for the [AppVeyor](https://www.appveyor.com) service.
 */
namespace coveralls\services\appveyor;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(): Configuration {
  $repoName = getenv('APPVEYOR_REPO_NAME');
  $serviceNumber = getenv('APPVEYOR_BUILD_VERSION');

  return new Configuration([
    'commit_sha' => getenv('APPVEYOR_REPO_COMMIT'),
    'service_branch' => getenv('APPVEYOR_REPO_BRANCH'),
    'service_build_url' => "https://ci.appveyor.com/project/$repoName/build/$serviceNumber",
    'service_job_id' => getenv('APPVEYOR_BUILD_ID'),
    'service_job_number' => getenv('APPVEYOR_BUILD_NUMBER'),
    'service_name' => 'appveyor',
    'service_number' => $serviceNumber
  ]);
}
