<?php
declare(strict_types=1);
namespace coveralls\services\travis_ci;

use coveralls\{Configuration};

/**
 * Gets the [Travis CI](https://travis-ci.com) configuration parameters from the environment.
 * @param array $env An array providing environment variables.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(array $env): Configuration {
  $config = new Configuration([
    'commit_sha' => 'HEAD',
    'service_branch' => $env['TRAVIS_BRANCH'] ?? null,
    'service_job_id' => $env['TRAVIS_JOB_ID'] ?? null,
    'service_name' => 'travis-ci'
  ]);

  $pullRequest = $env['TRAVIS_PULL_REQUEST'] ?? null;
  if ($pullRequest && $pullRequest != 'false') $config['service_pull_request'] = $pullRequest;

  return $config;
}
