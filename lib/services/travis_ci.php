<?php
/**
 * Provides a connector for the [Travis CI](https://travis-ci.com) service.
 */
namespace coveralls\services\travis_ci;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(): Configuration {
  $config = new Configuration([
    'commit_sha' => 'HEAD',
    'service_branch' => getenv('TRAVIS_BRANCH'),
    'service_job_id' => getenv('TRAVIS_JOB_ID'),
    'service_name' => 'travis-ci'
  ]);

  $pullRequest = getenv('TRAVIS_PULL_REQUEST');
  if ($pullRequest && $pullRequest != 'false') $config['service_pull_request'] = $pullRequest;

  return $config;
}
