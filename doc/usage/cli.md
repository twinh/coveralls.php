path: blob/master
source: bin/coveralls

# Command line interface
The easy way. From a command prompt, install the `coveralls` executable:

```shell
composer global require cedx/coveralls
```

> Consider adding the [`composer global`](https://getcomposer.org/doc/03-cli.md#global) executables directory to your system path.

Then use it to upload your coverage reports:

```shell
$ coveralls --help

Send a coverage report to the Coveralls service.

file
     The coverage report to upload.

--help
     Show the help page for this command.

-v/--version
     Output the version number.
```

For example:

```shell
coveralls build/coverage.xml
```
