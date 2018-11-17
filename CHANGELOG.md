# Changelog

## Version [9.1.2](https://github.com/cedx/coveralls.php/compare/v9.1.1...v9.1.2)
- Fixed the [GitHub issue #5](https://github.com/cedx/coveralls.php/issues/5): an invalid output URL was generated when using a well-formed input URL.

## Version [9.1.1](https://github.com/cedx/coveralls.php/compare/v9.1.0...v9.1.1)
- Improved the handling of SSH-based [Git](https://git-scm.com) remotes.

## Version [9.1.0](https://github.com/cedx/coveralls.php/compare/v9.0.1...v9.1.0)
- Replaced the [Phing](https://www.phing.info) build system by [Robo](https://robo.li).
- Updated the package dependencies.

## Version [9.0.1](https://github.com/cedx/coveralls.php/compare/v9.0.0...v9.0.1)
- Fixed the [GitHub issue #3](https://github.com/cedx/coveralls.php/issues/3): the CLI used the old signature of the `Client` constructor.

## Version [9.0.0](https://github.com/cedx/coveralls.php/compare/v8.0.0...v9.0.0)
- Breaking change: changed the signature of the `Client`, `ClientException` and `GitRemote` constructors.
- Breaking change: changed the signature of the `Job::setRunAt()` method.
- Breaking change: replaced the parser functions by classes.
- Breaking change: replaced the service functions by classes.
- Added support for [PHPStan](https://github.com/phpstan/phpstan) static analyzer.
- Updated the package dependencies.

## Version [8.0.0](https://github.com/cedx/coveralls.php/compare/v7.2.0...v8.0.0)
- Breaking change: changed the signature of the `fromJson()` methods.
- Updated the package dependencies.

## Version [7.2.0](https://github.com/cedx/coveralls.php/compare/v7.1.0...v7.2.0)
- Dropped the dependency on [PHPUnit-Expect](https://dev.belin.io/phpunit-expect).
- Updated the package dependencies.

## Version [7.1.0](https://github.com/cedx/coveralls.php/compare/v7.0.0...v7.1.0)
- Added an example code.
- Updated the package dependencies.

## Version [7.0.0](https://github.com/cedx/coveralls.php/compare/v6.0.0...v7.0.0)
- Breaking change: changed the signature of the `Client` events.
- Breaking change: raised the required [PHP](https://secure.php.net) version.
- Breaking change: using PHP 7.1 features, like nullable types and void functions.
- Added the `ClientException` class.
- Added a user guide based on [MkDocs](http://www.mkdocs.org).
- Updated the package dependencies.

## Version [6.0.0](https://github.com/cedx/coveralls.php/compare/v5.0.0...v6.0.0)
- Breaking change: changed the signature of most class constructors.
- Breaking change: most class properties are now read-only.
- Breaking change: the `Configuration::fromYaml()` method now throws an `InvalidArgumentException` if the document is invalid.
- Updated the package dependencies.

## Version [5.0.0](https://github.com/cedx/coveralls.php/compare/v4.0.0...v5.0.0)
- Breaking change: moved the `Observable` API to a synchronous one.
- Breaking change: moved the `Subject` event API to the `EventEmitter` one.
- Changed licensing for the [MIT License](https://opensource.org/licenses/MIT).
- Restored the [Guzzle](http://docs.guzzlephp.org) HTTP client.

## Version [4.0.0](https://github.com/cedx/coveralls.php/compare/v3.0.0...v4.0.0)
- Breaking change: properties representing URLs as strings now use instances of the [`Psr\Http\Message\UriInterface`](http://www.php-fig.org/psr/psr-7/#35-psrhttpmessageuriinterface) interface.
- Added new unit tests.
- Replaced the [Guzzle](http://docs.guzzlephp.org) HTTP client by an `Observable`-based one.

## Version [3.0.0](https://github.com/cedx/coveralls.php/compare/v2.0.1...v3.0.0)
- Breaking change: renamed the `coveralls` namespace to `Coveralls`.

## Version [2.0.1](https://github.com/cedx/coveralls.php/compare/v2.0.0...v2.0.1)
- Merged [pull request #1](https://github.com/cedx/coveralls.php/pull/1): fixes an issue with usages of the `array_filter()` function.

## Version [2.0.0](https://github.com/cedx/coveralls.php/compare/v1.0.0...v2.0.0)
- Breaking change: ported some APIs to [Observables](http://reactivex.io/intro.html).
- Breaking change: replaced the `-f|--file` named argument of the CLI script by an anonymous argument (e.g. `coveralls coverage.xml` instead of `coveralls -f coverage.xml`)
- Enabled the strict typing.
- Replaced [phpDocumentor](https://www.phpdoc.org) documentation generator by [ApiGen](https://github.com/ApiGen/ApiGen).
- Updated the package dependencies.

## Version [1.0.0](https://github.com/cedx/coveralls.php/compare/v0.4.0...v1.0.0)
- First stable release.
- Updated the package dependencies.

## Version [0.4.0](https://github.com/cedx/coveralls.php/compare/v0.3.0...v0.4.0)
- Breaking change: dropped the dependency on [Observables](http://reactivex.io/intro.html).
- Breaking change: the `Client` class is now an `EventEmitter`.
- Ported the unit test assertions from [TDD](https://en.wikipedia.org/wiki/Test-driven_development) to [BDD](https://en.wikipedia.org/wiki/Behavior-driven_development).
- Updated the package dependencies.

## Version [0.3.0](https://github.com/cedx/coveralls.php/compare/v0.2.0...v0.3.0)
- Updated the package dependencies.

## Version [0.2.0](https://github.com/cedx/coveralls.php/compare/v0.1.0...v0.2.0)
- Updated the package dependencies.

## Version 0.1.0
- Initial release.
