<?php declare(strict_types=1);
namespace Coveralls\Http;

use Coveralls\{Job};
use PHPUnit\Framework\{TestCase};

/** Tests the features of the `Coveralls\Http\Client` class. */
class ClientTest extends TestCase {

  /** @test Tests the `Client::upload()` method. */
  function testUpload(): void {
    // It should throw an exception with an empty coverage report.
    try {
      (new Client)->upload('');
      $this->fail('Exception not thrown.');
    }

    catch (\Throwable $e) {
      assertThat($e, isInstanceOf(\InvalidArgumentException::class));
    }

    // It should throw an error with an invalid coverage report.
    try {
      (new Client)->upload('end_of_record');
      $this->fail('Exception not thrown.');
    }

    catch (\Throwable $e) {
      assertThat($e, isInstanceOf(\InvalidArgumentException::class));
    }
  }

  /** @test Tests the `Client::uploadJob()` method. */
  function testUploadJob(): void {
    // It should throw an exception with an empty coverage job.
    try {
      (new Client)->uploadJob(new Job);
      $this->fail('Exception not thrown.');
    }

    catch (\Throwable $e) {
      assertThat($e, isInstanceOf(\InvalidArgumentException::class));
    }
  }
}
