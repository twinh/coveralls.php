<?php
/**
 * Provides a connector for the [Surf](https://github.com/surf-build/surf) service.
 */
namespace coveralls\services\surf;

/**
 * Gets the configuration parameters from the environment.
 * @return array The configuration parameters.
 */
function getConfiguration() {
  return [
    'git_branch' => getenv('SURF_REF'),
    'git_commit' => getenv('SURF_SHA1'),
    'service_name' => 'surf'
  ];
}
