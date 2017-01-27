<?php
/**
 * Implementation of the `coveralls\test\GitCommitTest` class.
 */
namespace coveralls\test;
use coveralls\{GitCommit};

/**
 * Tests the features of the `coveralls\GitCommit` class.
 */
class GitCommitTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the `GitCommit::fromJSON()` method.
   */
  public function testFromJSON() {
    $this->assertNull(GitCommit::fromJSON('foo'));

    $commit = GitCommit::fromJSON([]);
    $this->assertInstanceOf(GitCommit::class, $commit);
    $this->assertEmpty($commit->getId());

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
  }

  /**
   * Tests the `GitCommit::jsonSerialize()` method.
   */
  public function testJsonSerialize() {
    $map = (new GitCommit())->jsonSerialize();
    $this->assertCount(1, get_object_vars($map));
    $this->assertEmpty($map->id);

    $map = (new GitCommit('2ef7bde608ce5404e97d5f042f95f89f1c232871', 'Hello World!'))
      ->setAuthorEmail('anonymous@secret.com')
      ->setAuthorName('Anonymous')
      ->jsonSerialize();

    $this->assertCount(4, get_object_vars($map));
    $this->assertEquals('anonymous@secret.com', $map->author_email);
    $this->assertEquals('Anonymous', $map->author_name);
    $this->assertEquals('2ef7bde608ce5404e97d5f042f95f89f1c232871', $map->id);
    $this->assertEquals('Hello World!', $map->message);
  }
}
