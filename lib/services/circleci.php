<?php
/**
 * Provides a connector for the [CircleCI](https://circleci.com) service.
 */
namespace coveralls\services\circleci;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @param array $env An array providing environment variables.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(array $env): Configuration {
  $nodes = (int) ($env['CIRCLE_NODE_TOTAL'] ?? '0');
  return new Configuration([
    'commit_sha' => $env['CIRCLE_SHA1'] ?? null,
    'parallel' => $nodes > 1 ? 'true' : 'false',
    'service_branch' => $env['CIRCLE_BRANCH'] ?? null,
    'service_build_url' => $env['CIRCLE_BUILD_URL'] ?? null,
    'service_job_number' => $env['CIRCLE_NODE_INDEX'] ?? null,
    'service_name' => 'circleci',
    'service_number' => $env['CIRCLE_BUILD_NUM'] ?? null
  ]);
}
