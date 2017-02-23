<?php
/**
 * Implementation of the `coveralls\test\GitCommitTest` class.
 */
namespace coveralls\test;

use Codeception\{Specify};
use coveralls\{GitCommit};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \coveralls\GitCommit
 */
class GitCommitTest extends TestCase {
  use Specify;

  /**
   * @test ::fromJSON
   */
  public function testFromJSON() {
    $this->specify('should return a null reference with a non-object value', function() {
      $this->assertNull(GitCommit::fromJSON('foo'));
    });

    $this->specify('should return an instance with default values for an empty map', function() {
      $commit = GitCommit::fromJSON([]);
      $this->assertInstanceOf(GitCommit::class, $commit);
      $this->assertEmpty($commit->getId());
    });

    $this->specify('should return an initialized instance for a non-empty map', function() {
      $commit = GitCommit::fromJSON([
        'author_email' => 'anonymous@secret.com',
        'author_name' => 'Anonymous',
        'id' => '2ef7bde608ce5404e97d5f042f95f89f1c232871',
        'message' => 'Hello World!'
      ]);

      $this->assertInstanceOf(GitCommit::class, $commit);
      $this->assertEquals('anonymous@secret.com', $commit->getAuthorEmail());
      $this->assertEquals('Anonymous', $commit->getAuthorName());
      $this->assertEquals('2ef7bde608ce5404e97d5f042f95f89f1c232871', $commit->getId());
      $this->assertEquals('Hello World!', $commit->getMessage());
    });
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    $this->specify('should return a map with default values for a newly created instance', function() {
      $map = (new GitCommit())->jsonSerialize();
      $this->assertCount(1, get_object_vars($map));
      $this->assertEmpty($map->id);
    });

    $this->specify('should return a non-empty map for an initialized instance', function() {
      $map = (new GitCommit('2ef7bde608ce5404e97d5f042f95f89f1c232871', 'Hello World!'))
        ->setAuthorEmail('anonymous@secret.com')
        ->setAuthorName('Anonymous')
        ->jsonSerialize();

      $this->assertCount(4, get_object_vars($map));
      $this->assertEquals('anonymous@secret.com', $map->author_email);
      $this->assertEquals('Anonymous', $map->author_name);
      $this->assertEquals('2ef7bde608ce5404e97d5f042f95f89f1c232871', $map->id);
      $this->assertEquals('Hello World!', $map->message);
    });
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $commit = (string) new GitCommit('2ef7bde608ce5404e97d5f042f95f89f1c232871');

    $this->specify('should start with the class name', function() use ($commit) {
      $this->assertStringStartsWith('coveralls\GitCommit {', $commit);
    });

    $this->specify('should contain the instance properties', function() use ($commit) {
      $this->assertContains('"id":"2ef7bde608ce5404e97d5f042f95f89f1c232871"', $commit);
    });
  }
}
