<?php
/**
 * Provides a connector for the [Jenkins](https://jenkins.io) service.
 */
namespace coveralls\services\jenkins;

/**
 * Gets the configuration parameters from the environment.
 * @return array The configuration parameters.
 */
function getConfiguration() {
  return [
    'git_branch' => getenv('GIT_BRANCH'),
    'git_commit' => getenv('GIT_COMMIT'),
    'service_job_id' => getenv('BUILD_ID'),
    'service_name' => 'jenkins',
    'service_pull_request' => getenv('ghprbPullId')
  ];
}
