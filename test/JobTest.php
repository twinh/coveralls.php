<?php
/**
 * Implementation of the `coveralls\test\JobTest` class.
 */
namespace coveralls\test;

use coveralls\{GitData, Job, SourceFile};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \coveralls\Job
 */
class JobTest extends TestCase {

  /**
   * @test ::fromJSON
   */
  public function testFromJSON() {
    // Should return a null reference with a non-object value.
    $this->assertNull(Job::fromJSON('foo'));

    // Should return an instance with default values for an empty map.
    $job = Job::fromJSON([]);
    $this->assertInstanceOf(Job::class, $job);
    $this->assertNull($job->getGit());
    $this->assertFalse($job->isParallel());
    $this->assertEmpty($job->getRepoToken());
    $this->assertNull($job->getRunAt());
    $this->assertCount(0, $job->getSourceFiles());

    // Should return an initialized instance for a non-empty map.
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
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    // Should return a map with default values for a newly created instance.
    $map = (new Job())->jsonSerialize();
    $this->assertCount(1, get_object_vars($map));
    $this->assertCount(0, $map->source_files);

    // Should return a non-empty map for an initialized instance.
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
   * @test ::__toString
   */
  public function testToString() {
    $job = (string) new Job();

    // Should start with the class name.
    $this->assertStringStartsWith('coveralls\Job {', $job);

    // Should contain the instance properties.
    $this->assertContains('"source_files":[]', $job);
  }
}
