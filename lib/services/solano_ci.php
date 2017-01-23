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
  return new Configuration([
    // TODO Implement this function.
  ]);
}
