<?php
declare(strict_types = 1);
namespace coveralls\services\wercker;

use coveralls\{Configuration};

/**
 * Gets the [Wercker](http://www.wercker.com) configuration parameters from the environment.
 * @param array $env An array providing environment variables.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(array $env): Configuration {
  return new Configuration([
    'commit_sha' => $env['WERCKER_GIT_COMMIT'] ?? null,
    'service_branch' => $env['WERCKER_GIT_BRANCH'] ?? null,
    'service_job_id' => $env['WERCKER_BUILD_ID'] ?? null,
    'service_name' => 'wercker'
  ]);
}
