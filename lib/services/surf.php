<?php
/**
 * Provides a connector for the [Surf](https://github.com/surf-build/surf) service.
 */
namespace coveralls\services\surf;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(): Configuration {
  return new Configuration([
    'commit_sha' => getenv('SURF_SHA1'),
    'service_branch' => getenv('SURF_REF'),
    'service_name' => 'surf'
  ]);
}
