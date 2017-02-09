<?php
/**
 * Implementation of the `coveralls\test\ClientTest` class.
 */
namespace coveralls\test;

use coveralls\{Client, Configuration, GitData, Job, SourceFile};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \coveralls\Client
 */
class ClientTest extends TestCase {

  /**
   * @test ::parseCloverReport
   */
  public function testParseCloverReport() {
    $parseCloverReport = function(string $report) {
      return $this->parseCloverReport($report);
    };

    $job = $parseCloverReport->call(new Client(), @file_get_contents(__DIR__.'/fixtures/clover.xml'));
    $files = $job->getSourceFiles();
    $this->assertCount(3, $files);

    $this->assertInstanceOf(SourceFile::class, $files[0]);
    $this->assertEquals('lib/Client.php', $files[0]->getName());
    $this->assertNotEmpty($files[0]->getSourceDigest());

    $subset = [null, 2, 2, 2, 2, null];
    $this->assertEquals($subset, array_intersect($subset, $files[0]->getCoverage()->getArrayCopy()));

    $this->assertEquals('lib/Configuration.php', $files[1]->getName());
    $this->assertNotEmpty($files[1]->getSourceDigest());

    $subset = [null, 4, 4, 2, 2, 4, 2, 2, 4, 4, null];
    $this->assertEquals($subset, array_intersect($subset, $files[1]->getCoverage()->getArrayCopy()));

    $this->assertEquals('lib/GitCommit.php', $files[2]->getName());
    $this->assertNotEmpty($files[2]->getSourceDigest());

    $subset = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
    $this->assertEquals($subset, array_intersect($subset, $files[2]->getCoverage()->getArrayCopy()));

    $this->expectException(\InvalidArgumentException::class);
    $parseCloverReport->call(new Client(), '<project></project>');
  }

  /**
   * @test ::parseLcovReport
   */
  public function testParseLcovReport() {
    $parseLcovReport = function(string $report): Job {
      return $this->parseLcovReport($report);
    };

    $job = $parseLcovReport->call(new Client(), @file_get_contents(__DIR__.'/fixtures/lcov.info'));
    $files = $job->getSourceFiles();
    $this->assertCount(3, $files);

    $this->assertInstanceOf(SourceFile::class, $files[0]);
    $this->assertEquals('lib/Client.php', $files[0]->getName());
    $this->assertNotEmpty($files[0]->getSourceDigest());

    $subset = [null, 2, 2, 2, 2, null];
    $this->assertEquals($subset, array_intersect($subset, $files[0]->getCoverage()->getArrayCopy()));

    $this->assertEquals('lib/Configuration.php', $files[1]->getName());
    $this->assertNotEmpty($files[1]->getSourceDigest());

    $subset = [null, 4, 4, 2, 2, 4, 2, 2, 4, 4, null];
    $this->assertEquals($subset, array_intersect($subset, $files[1]->getCoverage()->getArrayCopy()));

    $this->assertEquals('lib/GitCommit.php', $files[2]->getName());
    $this->assertNotEmpty($files[2]->getSourceDigest());

    $subset = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
    $this->assertEquals($subset, array_intersect($subset, $files[2]->getCoverage()->getArrayCopy()));
  }

  /**
   * @test ::updateJob
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
   * @test ::upload
   */
  public function testUpload() {
    $this->expectException(\InvalidArgumentException::class);
    (new Client())->upload('');
  }

  /**
   * @test ::uploadJob
   */
  public function testUploadJob() {
    $this->expectException(\InvalidArgumentException::class);
    (new Client())->uploadJob(new Job());
  }
}
