path: blob/master
source: lib/Http/Client.php

# Application programming interface
The hard way. Use the `Coveralls\Http\Client` class to upload your coverage reports:

```php
<?php
use Coveralls\Http\{Client, ClientException};

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
if the input report is invalid. It throws a `Coveralls\Http\ClientException` if any error occurred while uploading the report.

## Client events
The `Coveralls\Http\Client` class is a [`League\Event\Emitter`](https://event.thephpleague.com/2.0/emitter/basic-usage) that triggers some events during its life cycle:

- `Client::EVENT_REQUEST` : emitted every time a request is made to the remote service.
- `Client::EVENT_RESPONSE` : emitted every time a response is received from the remote service.

You can subscribe to them using the `addListener()` method:

```php
<?php
use Coveralls\Http\{Client, RequestEvent, ResponseEvent};

function main(): void {
  $client = new Client;
  
  $client->addListener(Client::EVENT_REQUEST, function(RequestEvent $event) {
    echo 'Client request: ', $event->getRequest()->getUri();
  });

  $client->addListener(Client::EVENT_RESPONSE, function(ResponseEvent $event) {
    echo 'Server response: ', $event->getResponse()->getStatusCode();
  });
}
```
