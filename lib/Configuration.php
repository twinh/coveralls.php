<?php
/**
 * Implementation of the `coveralls\Configuration` class.
 */
namespace coveralls;

use coveralls\services\{
  appveyor,
  circleci,
  codeship,
  gitlab_ci,
  jenkins,
  surf,
  travis_ci,
  wercker
};

use Symfony\Component\Yaml\Yaml;

/**
 * Provides access to the coverage settings.
 */
class Configuration extends \ArrayObject {

  /**
   * Initializes a new instance of the class.
   * @param array $params The coverage parameters.
   */
  public function __construct(array $params = []) {
    parent::__construct($params);
  }

  /**
   * Creates a new configuration from the environment variables.
   * @return Configuration The newly created configuration.
   */
  public static function fromEnvironment(): self {
    $config = new static(['run_at' => getenv('COVERALLS_RUN_AT') ?: (new \DateTime())->format('c')]);

    if ($value = getenv('COVERALLS_GIT_BRANCH')) $config['git_branch'] = $value;
    if ($value = getenv('COVERALLS_GIT_COMMIT')) $config['git_commit'] = $value;
    if (getenv('COVERALLS_PARALLEL') !== false) $config['parallel'] = true;
    if ($value = getenv('COVERALLS_REPO_TOKEN')) $config['repo_token'] = $value;
    if ($value = getenv('COVERALLS_SERVICE_JOB_ID')) $config['service_job_id'] = $value;
    if ($value = getenv('COVERALLS_SERVICE_NAME')) $config['service_name'] = $value;

    /*
    $matches = new RegExp(r'(\d+)$').allMatches(getenv('CI_PULL_REQUEST') ?? '').toList();
    if ($matches.length >= 2) $config['service_pull_request'] = $matches[1].toString();
    */

    $assign = function(array $values) use ($config) {
      foreach ($values as $key => $value) { $config[$key] = $value; }
    };

    if (getenv('TRAVIS')) $assign(travis_ci\getConfiguration());
    else if (getenv('APPVEYOR')) $assign(appveyor\getConfiguration());
    else if (getenv('CIRCLECI')) $assign(circleci\getConfiguration());
    else if (getenv('CI_NAME') == 'codeship') $assign(codeship\getConfiguration());
    else if (getenv('GITLAB_CI')) $assign(gitlab_ci\getConfiguration());
    else if (getenv('JENKINS_URL')) $assign(jenkins\getConfiguration());
    else if (getenv('SURF_SHA1')) $assign(surf\getConfiguration());
    else if (getenv('WERCKER')) $assign(wercker\getConfiguration());

    return $config;
  }

  /**
   * Creates a new configuration from the specified YAML document.
   * @param string $document The YAML document.
   * @return Configuration The newly created configuration.
   */
  public static function fromYAML(string $document): self {
    return new static(Yaml::parse($document));
  }
}
