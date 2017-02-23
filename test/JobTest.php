<?php
/**
 * Implementation of the `coveralls\test\JobTest` class.
 */
namespace coveralls\test;

use Codeception\{Specify};
use coveralls\{GitData, Job, SourceFile};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \coveralls\Job
 */
class JobTest extends TestCase {
  use Specify;

  /**
   * @test ::fromJSON
   */
  public function testFromJSON() {
    $this->specify('should return a null reference with a non-object value', function() {
      $this->assertNull(Job::fromJSON('foo'));
    });

    $this->specify('should return an instance with default values for an empty map', function() {
      $job = Job::fromJSON([]);
      $this->assertInstanceOf(Job::class, $job);
      $this->assertNull($job->getGit());
      $this->assertFalse($job->isParallel());
      $this->assertEmpty($job->getRepoToken());
      $this->assertNull($job->getRunAt());
      $this->assertCount(0, $job->getSourceFiles());
    });

    $this->specify('should return an initialized instance for a non-empty map', function() {
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
    });
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    $this->specify('should return a map with default values for a newly created instance', function() {
      $map = (new Job())->jsonSerialize();
      $this->assertCount(1, get_object_vars($map));
      $this->assertCount(0, $map->source_files);
    });

    $this->specify('should return a non-empty map for an initialized instance', function() {
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
    });
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $job = (string) new Job();

    $this->specify('should start with the class name', function() use ($job) {
      $this->assertStringStartsWith('coveralls\Job {', $job);
    });

    $this->specify('should contain the instance properties', function() use ($job) {
      $this->assertContains('"source_files":[]', $job);
    });
  }
}
