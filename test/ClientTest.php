<?php declare(strict_types=1);
namespace Coveralls;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/** @testdox Coveralls\Client */
class ClientTest extends TestCase {

  /** @testdox ->upload() */
  function testUpload(): void {
    it('should throw an error with an invalid coverage report', function() {
      expect(fn() => (new Client)->upload('end_of_record'))->to->throw(\InvalidArgumentException::class);
    });
  }

  /** @testdox ->uploadJob() */
  function testUploadJob(): void {
    it('should throw an exception with an empty coverage job', function() {
      expect(fn() => (new Client)->uploadJob(new Job))->to->throw(\InvalidArgumentException::class);
    });
  }
}
