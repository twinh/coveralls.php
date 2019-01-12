path: blob/master
source: lib/Client.php

# Application programming interface
The hard way. Use the `Coveralls\Client` class to upload your coverage reports:

```php
<?php
use Coveralls\{Client, ClientException};

function main(): void {
  try {
    $coverage = file_get_contents('/path/to/coverage.report');
    (new Client)->upload($coverage);
    echo 'The report was sent successfully.';
  }

  catch (\Throwable $e) {
    echo 'An error occurred: ', $e->getMessage();
    if ($e instanceof ClientException) echo 'From: ', $e->getUri(), PHP_EOL;
  }
}
```

The `Client::upload()` method throws an [`InvalidArgumentException`](https://secure.php.net/manual/en/class.invalidargumentexception.php)
if the input report is invalid. It throws a `Coveralls\ClientException` if any error occurred while uploading the report.

## Client events
The `Coveralls\Client` class is an [`EventEmitter`](https://github.com/igorw/evenement/blob/master/src/Evenement/EventEmitterInterface.php) that triggers some events during its life cycle:

- `Client::EVENT_REQUEST` : emitted every time a request is made to the remote service.
- `Client::EVENT_RESPONSE` : emitted every time a response is received from the remote service.

You can subscribe to them using the `on()` method:

```php
<?php
use Coveralls\{Client};
use Psr\Http\Message\{RequestInterface, ResponseInterface};

function main(): void {
  $client = new Client;
  
  $client->on(Client::EVENT_REQUEST, function(RequestInterface $request) {
    echo 'Client request: ', $request->getUri();
  });

  $client->on(Client::EVENT_RESPONSE, function($request, ResponseInterface $response) {
    echo 'Server response: ', $response->getStatusCode();
  });
}
```
