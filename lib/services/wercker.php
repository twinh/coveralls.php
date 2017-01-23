<?php
/**
 * Provides a connector for the [Wercker](http://www.wercker.com) service.
 */
namespace coveralls\services\wercker;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(): Configuration {
  return new Configuration([
    'commit_sha' => getenv('WERCKER_GIT_COMMIT'),
    'service_branch' => getenv('WERCKER_GIT_BRANCH'),
    'service_job_id' => getenv('WERCKER_BUILD_ID'),
    'service_name' => 'wercker'
  ]);
}
