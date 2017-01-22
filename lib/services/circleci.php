<?php
/**
 * Provides a connector for the [CircleCI](https://circleci.com) service.
 */
namespace coveralls\services\circleci;

/**
 * Gets the configuration parameters from the environment.
 * @return array The configuration parameters.
 */
function getConfiguration() {
  $map = [
    'git_branch' => getenv('CIRCLE_BRANCH'),
    'git_commit' => getenv('CIRCLE_SHA1'),
    'service_job_id' => getenv('CIRCLE_BUILD_NUM'),
    'service_name' => 'circleci'
  ];

  if ($pullRequest = getenv('CI_PULL_REQUEST')) {
    $parts = explode('/pull/', $pullRequest);
    if (count($parts) >= 2) $map['service_pull_request'] = $parts[1];
  }

  return $map;
}
