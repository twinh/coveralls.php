<?php
/**
 * Provides a connector for the [Codeship](https://codeship.com) service.
 */
namespace coveralls\services\codeship;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(): Configuration {
  return new Configuration([
    'commit_sha' => getenv('CI_COMMIT_ID'),
    'git_committer_email' => getenv('CI_COMMITTER_EMAIL'),
    'git_committer_name' => getenv('CI_COMMITTER_NAME'),
    'git_message' => getenv('CI_COMMIT_MESSAGE'),
    'service_branch' => getenv('CI_BRANCH'),
    'service_job_id' => getenv('CI_BUILD_NUMBER'),
    'service_name' => 'codeship'
  ]);
}
