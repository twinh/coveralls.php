<?php
/**
 * Implementation of the `coveralls\test\ClientTest` class.
 */
namespace coveralls\test;
use coveralls\{Client, Job};

/**
 * Tests the features of the `coveralls\Client` class.
 */
class ClientTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the `Client::parseCloverReport()` method.
   */
  public function testParseCloverReport() {
    $parseCloverReport = function(string $coverage) {
      return $this->parseCloverReport($coverage);
    };

    // TODO: $job = $parseCloverReport->call(new Client());
  }

  /**
   * Tests the `Client::parseLcovReport()` method.
   */
  public function testParseLcovReport() {
    $parseLcovReport = function(string $coverage) {
      return $this->parseLcovReport($coverage);
    };

    // TODO: $job = $parseLcovReport->call(new Client());
  }

  /**
   * Tests the `Client::upload()` method.
   */
  public function testUpload() {
    $this->expectException(\InvalidArgumentException::class);
    (new Client())->upload('');
  }

  /**
   * Tests the `Client::uploadJob()` method.
   */
  public function testUploadJob() {
    $this->expectException(\InvalidArgumentException::class);
    (new Client())->uploadJob(new Job());
  }
}
