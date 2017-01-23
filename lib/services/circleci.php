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
    'commit_sha' => getenv('CIRCLE_SHA1'),
    'service_branch' => getenv('CIRCLE_BRANCH'),
    'parallel' => ((int) getenv('CIRCLE_NODE_TOTAL')) > 1 ? 'true' : 'false',
    'service_job_number' => getenv('CIRCLE_NODE_INDEX'),
    'service_name' => 'circleci',
    'service_number' => getenv('CIRCLE_BUILD_NUM')
  ]);

  if ($pullRequest = getenv('CI_PULL_REQUEST')) {
    $parts = explode('/pull/', $pullRequest);
    if (count($parts) >= 2) $config['service_pull_request'] = $parts[1];
  }

  return $config;
}
