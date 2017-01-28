<?php
/**
 * Provides a connector for the [Jenkins](https://jenkins.io) service.
 */
namespace coveralls\services\jenkins;
use coveralls\{Configuration};

/**
 * Gets the configuration parameters from the environment.
 * @param array $env An array providing environment variables.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(array $env): Configuration {
  return new Configuration([
    'commit_sha' => $env['GIT_COMMIT'] ?? null,
    'service_branch' => $env['GIT_BRANCH'] ?? null,
    'service_build_url' => $env['BUILD_URL'] ?? null,
    'service_job_id' => $env['BUILD_ID'] ?? null,
    'service_name' => 'jenkins',
    'service_number' => $env['BUILD_NUMBER'] ?? null,
    'service_pull_request' => $env['ghprbPullId'] ?? null
  ]);
}
