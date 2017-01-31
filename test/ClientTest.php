<?php
/**
 * Implementation of the `coveralls\test\ClientTest` class.
 */
namespace coveralls\test;
use coveralls\{Client, Configuration, GitData, Job};

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
   * Tests the `Client::parseCoverage()` method.
   */
  public function testParseCoverage() {
    $parseCoverage = function(string $coverage) {
      return $this->parseCoverage($coverage);
    };

    // TODO: $job = $parseCoverage->call(new Client());
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
   * Tests the `Client::updateJob()` method.
   */
  public function testUpdateJob() {
    $client = new Client();
    $job = new Job();
    $updateJob = function(Job $job, Configuration $config) {
      $this->updateJob($job, $config);
    };

    $updateJob->call($client, $job, new Configuration());
    $this->assertNull($job->getGit());
    $this->assertFalse($job->isParallel());
    $this->assertEmpty($job->getRepoToken());
    $this->assertNull($job->getRunAt());

    $updateJob->call($client, $job, new Configuration([
      'parallel' => 'true',
      'repo_token' => 'yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt',
      'run_at' => '2017-01-29T03:43:30+01:00',
      'service_branch' => 'develop'
    ]));

    $this->assertTrue($job->isParallel());
    $this->assertEquals('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt', $job->getRepoToken());

    $git = $job->getGit();
    $this->assertInstanceOf(GitData::class, $git);
    $this->assertEquals('develop', $git->getBranch());

    $runAt = $job->getRunAt();
    $this->assertInstanceOf(\DateTime::class, $runAt);
    $this->assertEquals('2017-01-29T03:43:30+01:00', $runAt->format('c'));
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
