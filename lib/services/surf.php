<?php
/**
 * Provides a connector for the [Surf](https://github.com/surf-build/surf) service.
 */
namespace coveralls\services\surf;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @param array $env An array providing environment variables.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(array $env): Configuration {
  return new Configuration([
    'commit_sha' => $env['SURF_SHA1'] ?? null,
    'service_branch' => $env['SURF_REF'] ?? null,
    'service_name' => 'surf'
  ]);
}
