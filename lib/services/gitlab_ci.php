<?php
/**
 * Provides a connector for the [GitLab CI](https://gitlab.com) service.
 */
namespace coveralls\services\gitlab_ci;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @param array $env An array providing environment variables.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(array $env): Configuration {
  return new Configuration([
    'commit_sha' => $env['CI_BUILD_REF'] ?? null,
    'service_branch' => $env['CI_BUILD_REF_NAME'] ?? null,
    'service_job_id' => $env['CI_BUILD_ID'] ?? null,
    'service_job_number' => $env['CI_BUILD_NAME'] ?? null,
    'service_name' => 'gitlab-ci'
  ]);
}
