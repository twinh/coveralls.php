# Coveralls for PHP
![Runtime](https://img.shields.io/badge/php-%3E%3D7.0-brightgreen.svg) ![Release](https://img.shields.io/packagist/v/cedx/coveralls.svg) ![License](https://img.shields.io/packagist/l/cedx/coveralls.svg) ![Downloads](https://img.shields.io/packagist/dt/cedx/coveralls.svg) ![Coverage](https://coveralls.io/repos/github/cedx/coveralls.php/badge.svg) ![Build](https://travis-ci.org/cedx/coveralls.php.svg)

Send [Clover](https://www.atlassian.com/software/clover) and [LCOV](http://ltp.sourceforge.net/coverage/lcov.php) coverage reports to the [Coveralls](https://coveralls.io) service, in [PHP](https://secure.php.net).

## Requirements
The latest [PHP](https://secure.php.net) and [Composer](https://getcomposer.org) versions.
If you plan to play with the sources, you will also need the latest [Phing](https://www.phing.info) version.

## Usage

### Command line interface
The easy way. From a command prompt, install the `coveralls` executable:

```shell
$ composer global require cedx/coveralls
```

> Consider adding the [`composer global`](https://getcomposer.org/doc/03-cli.md#global) executables directory to your system path.

Then use it to upload your coverage reports:

```shell
$ coveralls --help

Send Clover and LCOV coverage reports to the Coveralls service.

file
     The coverage report to upload.

--help
     Show the help page for this command.

-v/--version
     Output the version number.
```

For example:

```shell
$ coveralls build/coverage.xml
```

### Programming interface
The hard way. From a command prompt, install the library:
              
```shell
$ composer require cedx/coveralls
```

Now, in your [PHP](https://secure.php.net) code, you can use the `Coveralls\Client` class to upload your coverage reports:

```php
use Coveralls\{Client};

try {
  $coverage = file_get_contents('/path/to/coverage.report');
  (new Client)->upload($coverage);
}

catch (\Throwable $e) {
  echo 'An error occurred: ', $e->getMessage();
}
```

## Supported coverage formats
Currently, this package supports two formats of coverage reports:
- [Clover](https://www.atlassian.com/software/clover): the main format used with [PHPUnit](https://phpunit.de).
- [LCOV](http://ltp.sourceforge.net/coverage/lcov.php): the de facto standard.

## Supported CI services
This project has been tested with [Travis CI](https://travis-ci.com) service, but these services should also work with no extra effort:
- [AppVeyor](https://www.appveyor.com)
- [CircleCI](https://circleci.com)
- [Codeship](https://codeship.com)
- [GitLab CI](https://gitlab.com)
- [Jenkins](https://jenkins.io)
- [Semaphore](https://semaphoreci.com)
- [Solano CI](https://ci.solanolabs.com)
- [Surf](https://github.com/surf-build/surf)
- [Wercker](http://www.wercker.com)

## Environment variables
If your build system is not supported, you can still use this package.
There are a few environment variables that are necessary for supporting your build system:
- `COVERALLS_SERVICE_NAME` : the name of your build system.
- `COVERALLS_REPO_TOKEN` : the secret repository token from [Coveralls](https://coveralls.io).

There are optional environment variables:
- `COVERALLS_SERVICE_JOB_ID` : a string that uniquely identifies the build job.
- `COVERALLS_RUN_AT` : a date string for the time that the job ran. This defaults to your build system's date/time if you don't set it.

The full list of supported environment variables is available in the source code of the `Coveralls\Configuration` class (see the `fromEnvironment()` static method).

## The `.coveralls.yml` file
This package supports the same configuration sources as the [Coveralls](https://coveralls.io) ones:  
[Coveralls currently supports](https://coveralls.zendesk.com/hc/en-us/articles/201347419-Coveralls-currently-supports)

## See also
- [API reference](https://cedx.github.io/coveralls.php)
- [Code coverage](https://coveralls.io/github/cedx/coveralls.php)
- [Continuous integration](https://travis-ci.org/cedx/coveralls.php)

## License
[Coveralls for PHP](https://github.com/cedx/coveralls.php) is distributed under the MIT License.
