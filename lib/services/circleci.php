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
  return new Configuration([
    'commit_sha' => getenv('CIRCLE_SHA1'),
    'parallel' => ((int) getenv('CIRCLE_NODE_TOTAL')) > 1 ? 'true' : 'false',
    'service_branch' => getenv('CIRCLE_BRANCH'),
    'service_build_url' => getenv('CIRCLE_BUILD_URL'),
    'service_job_number' => getenv('CIRCLE_NODE_INDEX'),
    'service_name' => 'circleci',
    'service_number' => getenv('CIRCLE_BUILD_NUM')
  ]);
}
