<?php
/**
 * Provides a connector for the [GitLab CI](https://gitlab.com) service.
 */
namespace coveralls\services\gitlab_ci;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(): Configuration {
  return new Configuration([
    'commit_sha' => getenv('CI_BUILD_REF'),
    'service_branch' => getenv('CI_BUILD_REF_NAME'),
    'service_job_id' => getenv('CI_BUILD_ID'),
    'service_job_number' => getenv('CI_BUILD_NAME'),
    'service_name' => 'gitlab-ci'
  ]);
}
