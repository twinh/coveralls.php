<?php
/**
 * Provides a connector for the [Travis CI](https://travis-ci.com) service.
 */
namespace coveralls\services\travis_ci;

/**
 * Gets the configuration parameters from the environment.
 * @return array The configuration parameters.
 */
function getConfiguration(): array {
  return [
    'git_branch' => getenv('TRAVIS_BRANCH'),
    'git_commit' => 'HEAD',
    'service_job_id' => getenv('TRAVIS_JOB_ID'),
    'service_name' => 'travis-ci'
  ];
}
