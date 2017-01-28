<?php
/**
 * Provides a connector for the [Codeship](https://codeship.com) service.
 */
namespace coveralls\services\codeship;
use coveralls\{Configuration};

/**
 * Gets the configuration parameters from the environment.
 * @param array $env An array providing environment variables.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(array $env): Configuration {
  return new Configuration([
    'commit_sha' => $env['CI_COMMIT_ID'] ?? null,
    'git_committer_email' => $env['CI_COMMITTER_EMAIL'] ?? null,
    'git_committer_name' => $env['CI_COMMITTER_NAME'] ?? null,
    'git_message' => $env['CI_COMMIT_MESSAGE'] ?? null,
    'service_job_id' => $env['CI_BUILD_NUMBER'] ?? null,
    'service_name' => 'codeship'
  ]);
}
