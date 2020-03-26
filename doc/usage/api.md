path: blob/master
source: lib/Client.php

# Application programming interface
The hard way. Use the `Coveralls\Client` class to upload your coverage reports:

```php
<?php
use Coveralls\{Client, ClientException};

function main(): void {
  try {
    $coverage = @file_get_contents('/path/to/coverage.report');
    (new Client)->upload($coverage);
    echo 'The report was sent successfully.';
  }

  catch (Throwable $e) {
    echo 'An error occurred: ', $e->getMessage();
    if ($e instanceof ClientException) echo 'From: ', $e->getUri(), PHP_EOL;
  }
}
```

The `Client->upload()` method throws an [`InvalidArgumentException`](https://www.php.net/manual/en/class.invalidargumentexception.php)
if the input report is invalid. It throws a `Coveralls\ClientException` if any error occurred while uploading the report.

## Client events
The `Coveralls\Client` class triggers some events during its life cycle:

- `request` : emitted every time a request is made to the remote service.
- `response` : emitted every time a response is received from the remote service.

You can subscribe to these events using the `on<EventName>()` methods:

```php
<?php
use Coveralls\{Client, RequestEvent, ResponseEvent};

function main(): void {
  $client = new Client;

  $client->onRequest(function(RequestEvent $event) {
    echo 'Client request: ', $event->getRequest()->getUri();
  });

  $client->onResponse(function(ResponseEvent $event) {
    echo 'Server response: ', $event->getResponse()->getStatusCode();
  });
}
```
