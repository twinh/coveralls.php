<?php
/**
 * Implementation of the `coveralls\test\ClientTest` class.
 */
namespace coveralls\test;
use coveralls\{Client};

/**
 * Tests the features of the `coveralls\Client` class.
 */
class ClientTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the constructor.
   */
  /*
  public function testConstruct() {
    $job = new Client();
    $this->assertNull($job->getGit());
    $this->assertFalse($job->isParallel());
    $this->assertEmpty($job->getRepoToken());
    $this->assertNull($job->getRunAt());
    $this->assertCount(0, $job->getSourceFiles());

    $job = new Client(new Configuration([
      'parallel' => 'true',
      'repo_token' => 'yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt',
      'run_at' => '2017-01-29T03:43:30+01:00',
      'service_branch' => 'develop'
    ]), [new SourceFile('/home/cedx/coveralls.php')]);

    $this->assertTrue($job->isParallel());
    $this->assertEquals('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt', $job->getRepoToken());

    $git = $job->getGit();
    $this->assertInstanceOf(GitData::class, $git);
    $this->assertEquals('develop', $git->getBranch());

    $runAt = $job->getRunAt();
    $this->assertInstanceOf(\DateTime::class, $runAt);
    $this->assertEquals('2017-01-29T03:43:30+01:00', $runAt->format('c'));

    $sourceFiles = $job->getSourceFiles();
    $this->assertCount(1, $sourceFiles);
    $this->assertInstanceOf(SourceFile::class, $sourceFiles[0]);
    $this->assertEquals('/home/cedx/coveralls.php', $sourceFiles[0]->getName());
  }*/
}
