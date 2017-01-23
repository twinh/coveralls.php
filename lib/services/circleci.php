<?php
/**
 * Provides a connector for the [CircleCI](https://circleci.com) service.
 */
namespace coveralls\services\circleci;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(): Configuration {
  $config = new Configuration([
    'git_branch' => getenv('CIRCLE_BRANCH'),
    'git_commit' => getenv('CIRCLE_SHA1'),
    'service_job_id' => getenv('CIRCLE_BUILD_NUM'),
    'service_name' => 'circleci'
  ]);

  if ($pullRequest = getenv('CI_PULL_REQUEST')) {
    $parts = explode('/pull/', $pullRequest);
    if (count($parts) >= 2) $config['service_pull_request'] = $parts[1];
  }

  return $config;
}
