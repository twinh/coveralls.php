<?php
/**
 * Provides a connector for the [Solano CI](https://ci.solanolabs.com) service.
 */
namespace coveralls\services\solano_ci;
use coveralls\Configuration;

/**
 * Gets the configuration parameters from the environment.
 * @return Configuration The configuration parameters.
 */
function getConfiguration(): Configuration {
  $serviceNumber = getenv('TDDIUM_SESSION_ID');
  return new Configuration([
    'service_branch' => getenv('TDDIUM_CURRENT_BRANCH'),
    'service_build_url' => "https://ci.solanolabs.com/reports/$serviceNumber",
    'service_job_number' => getenv('TDDIUM_TID'),
    'service_name' => 'tddium',
    'service_number' => $serviceNumber,
    'service_pull_request' => getenv('TDDIUM_PR_ID')
  ]);
}
