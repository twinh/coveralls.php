<?php declare(strict_types=1);
namespace Coveralls\Http;

use function PHPUnit\Expect\{expect, it};
use Coveralls\{Job};
use PHPUnit\Framework\{TestCase};

/** Tests the features of the `Coveralls\Http\Client` class. */
class ClientTest extends TestCase {

  /** @test Client->upload() */
  function testUpload(): void {
    it('should throw an exception with an empty coverage report', function() {
      expect(function() { (new Client)->upload(''); })->to->throw(\InvalidArgumentException::class);
    });

    it('should throw an error with an invalid coverage report', function() {
      expect(function() { (new Client)->upload('end_of_record'); })->to->throw(\InvalidArgumentException::class);
    });
  }

  /** @test Client->uploadJob() */
  function testUploadJob(): void {
    it('should throw an exception with an empty coverage job', function() {
      expect(function() { (new Client)->uploadJob(new Job); })->to->throw(\InvalidArgumentException::class);
    });
  }
}
