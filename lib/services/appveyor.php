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
  return new Configuration([
    'git_branch' => getenv('APPVEYOR_REPO_BRANCH'),
    'git_commit' => getenv('APPVEYOR_REPO_COMMIT'),
    'service_job_id' => getenv('APPVEYOR_BUILD_ID'),
    'service_job_number' => getenv('APPVEYOR_BUILD_NUMBER'),
    'service_name' => 'appveyor'
  ]);
}
