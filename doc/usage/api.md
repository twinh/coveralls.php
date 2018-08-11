path: blob/master
source: lib/Client.php

# Application programming interface
The hard way. Use the `Coveralls\Client` class to upload your coverage reports:

```php
<?php
use Coveralls\{Client, ClientException};

try {
  $coverage = file_get_contents('/path/to/coverage.report');
  (new Client)->upload($coverage);
  echo 'The report was sent successfully.';
}

catch (\Throwable $e) {
  echo 'An error occurred: ', $e->getMessage();
  if ($e instanceof ClientException) echo 'From: ', $e->getUri(), PHP_EOL;
}
```

The `Client::upload()` method throws an [`InvalidArgumentException`](https://secure.php.net/manual/en/class.invalidargumentexception.php)
if the input report is invalid. It throws a `Coveralls\ClientException` if any error occurred while uploading the report.
