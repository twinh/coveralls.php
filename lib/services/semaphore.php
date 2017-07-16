<?php
declare(strict_types = 1);
namespace coveralls\services\semaphore;

use coveralls\{Configuration};

/**
 * Gets the [Semaphore](https://semaphoreci.com) configuration parameters from the environment.
 * @param array $env An array providing environment variables.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(array $env): Configuration {
  return new Configuration([
    'commit_sha' => $env['REVISION'] ?? null,
    'service_branch' => $env['BRANCH_NAME'] ?? null,
    'service_name' => 'semaphore',
    'service_number' => $env['SEMAPHORE_BUILD_NUMBER'] ?? null,
    'service_pull_request' => $env['PULL_REQUEST_NUMBER'] ?? null
  ]);
}
