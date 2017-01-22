<?php
/**
 * Provides a connector for the [GitLab CI](https://gitlab.com) service.
 */
namespace coveralls\services\gitlab_ci;

/**
 * Gets the configuration parameters from the environment.
 * @return array The configuration parameters.
 */
function getConfiguration(): array {
  return [
    'git_branch' => getenv('CI_BUILD_REF_NAME'),
    'git_commit' => getenv('CI_BUILD_REF'),
    'service_job_id' => getenv('CI_BUILD_ID'),
    'service_job_number' => getenv('CI_BUILD_NAME'),
    'service_name' => 'gitlab-ci'
  ];
}
