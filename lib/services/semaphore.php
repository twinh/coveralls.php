<?php
/**
 * Provides a connector for the [Semaphore](https://semaphoreci.com) service.
 */
namespace coveralls\services\semaphore;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(): Configuration {
  return new Configuration([
    'commit_sha' => getenv('REVISION'),
    'service_branch' => getenv('BRANCH_NAME'),
    'service_name' => 'semaphore',
    'service_number' => getenv('SEMAPHORE_BUILD_NUMBER'),
    'service_pull_request' => getenv('PULL_REQUEST_NUMBER')
  ]);
}
