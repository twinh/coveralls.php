<?php
/**
 * Implementation of the `coveralls\test\JobTest` class.
 */
namespace coveralls\test;

use coveralls\{GitData, Job, SourceFile};
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `coveralls\Job` class.
 */
class JobTest extends TestCase {

  /**
   * Tests the `Job::fromJSON()` method.
   */
  public function testFromJSON() {
    $this->assertNull(Job::fromJSON('foo'));

    $job = Job::fromJSON([]);
    $this->assertInstanceOf(Job::class, $job);
    $this->assertNull($job->getGit());
    $this->assertFalse($job->isParallel());
    $this->assertEmpty($job->getRepoToken());
    $this->assertNull($job->getRunAt());
    $this->assertCount(0, $job->getSourceFiles());

    $job = Job::fromJSON([
      'git' => ['branch' => 'develop'],
      'parallel' => true,
      'repo_token' => 'yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt',
      'run_at' => '2017-01-29T03:43:30+01:00',
      'source_files' => [
        ['name' => '/home/cedx/coveralls.php']
      ]
    ]);

    $this->assertInstanceOf(Job::class, $job);
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
  }

  /**
   * Tests the `Job::jsonSerialize()` method.
   */
  public function testJsonSerialize() {
    $map = (new Job())->jsonSerialize();
    $this->assertCount(1, get_object_vars($map));
    $this->assertCount(0, $map->source_files);

    $map = (new Job())
      ->setGit(new GitData(null, 'develop'))
      ->setParallel(true)
      ->setRepoToken('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt')
      ->setRunAt('2017-01-29T03:43:30+01:00')
      ->setSourceFiles([new SourceFile('/home/cedx/coveralls.php')])
      ->jsonSerialize();

    $this->assertCount(5, get_object_vars($map));
    $this->assertTrue($map->parallel);
    $this->assertEquals('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt', $map->repo_token);
    $this->assertEquals('2017-01-29T03:43:30+01:00', $map->run_at);

    $this->assertInstanceOf(\stdClass::class, $map->git);
    $this->assertEquals('develop', $map->git->branch);

    $this->assertCount(1, $map->source_files);
    $this->assertInstanceOf(\stdClass::class, $map->source_files[0]);
    $this->assertEquals('/home/cedx/coveralls.php', $map->source_files[0]->name);
  }

  /**
   * Tests the `Job::__toString()` method.
   */
  public function testToString() {
    $job = (string) new Job();
    $this->assertStringStartsWith('coveralls\Job {', $job);
    $this->assertContains('"source_files":[]', $job);
  }
}
