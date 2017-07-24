# Changelog
This file contains highlights of what changes on each version of the [Coveralls for PHP](https://github.com/cedx/coveralls.php) package.

## Version 2.0.1
- Merged [pull request #1](https://github.com/cedx/coveralls.php/pull/1): fixes an issue with usages of the `array_filter()` function.

## Version 2.0.0
- Breaking change: ported some APIs to [Observables](http://reactivex.io/intro.html).
- Breaking change: replaced the `-f|--file` named argument of the CLI script by an anonymous argument (e.g. `coveralls coverage.xml` instead of `coveralls -f coverage.xml`)
- Enabled the strict typing.
- Replaced [phpDocumentor](https://www.phpdoc.org) documentation generator by [ApiGen](https://github.com/ApiGen/ApiGen).
- Updated the package dependencies.

## Version 1.0.0
- First stable release.
- Updated the package dependencies.

## Version 0.4.0
- Breaking change: dropped the dependency on [Observables](http://reactivex.io/intro.html).
- Breaking change: the `Client` class is now an `EventEmitter`.
- Ported the unit test assertions from [TDD](https://en.wikipedia.org/wiki/Test-driven_development) to [BDD](https://en.wikipedia.org/wiki/Behavior-driven_development).
- Updated the package dependencies.

## Version 0.3.0
- Updated the package dependencies.

## Version 0.2.0
- Updated the package dependencies.

## Version 0.1.0
- Initial release.
