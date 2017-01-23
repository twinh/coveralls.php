<?php
/**
 * Provides a connector for the [Jenkins](https://jenkins.io) service.
 */
namespace coveralls\services\jenkins;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(): Configuration {
  return new Configuration([
    'commit_sha' => getenv('GIT_COMMIT'),
    'service_branch' => getenv('GIT_BRANCH'),
    'service_build_url' => getenv('BUILD_URL'),
    'service_job_id' => getenv('BUILD_ID'),
    'service_name' => 'jenkins',
    'service_number' => getenv('BUILD_NUMBER'),
    'service_pull_request' => getenv('ghprbPullId')
  ]);
}
