<?php
/**
 * Implementation of the `coveralls\test\GitRemoteTest` class.
 */
namespace coveralls\test;

use Codeception\{Specify};
use coveralls\{GitRemote};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \coveralls\GitRemote
 */
class GitRemoteTest extends TestCase {
  use Specify;

  /**
   * @test ::fromJSON
   */
  public function testFromJSON() {
    $this->specify('should return a null reference with a non-object value', function() {
      $this->assertNull(GitRemote::fromJSON('foo'));
    });

    $this->specify('should return an instance with default values for an empty map', function() {
      $remote = GitRemote::fromJSON([]);
      $this->assertInstanceOf(GitRemote::class, $remote);
      $this->assertEmpty($remote->getName());
      $this->assertEmpty($remote->getURL());
    });

    $this->specify('should return an initialized instance for a non-empty map', function() {
      $remote = GitRemote::fromJSON(['name' => 'origin', 'url' => 'https://github.com/cedx/coveralls.php.git']);
      $this->assertInstanceOf(GitRemote::class, $remote);
      $this->assertEquals('origin', $remote->getName());
      $this->assertEquals('https://github.com/cedx/coveralls.php.git', $remote->getURL());
    });
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    $this->specify('should return a map with default values for a newly created instance', function() {
      $map = (new GitRemote())->jsonSerialize();
      $this->assertCount(2, get_object_vars($map));
      $this->assertEmpty($map->name);
      $this->assertEmpty($map->url);
    });

    $this->specify('should return a non-empty map for an initialized instance', function() {
      $map = (new GitRemote('origin', 'https://github.com/cedx/coveralls.php.git'))->jsonSerialize();
      $this->assertCount(2, get_object_vars($map));
      $this->assertEquals('origin', $map->name);
      $this->assertEquals('https://github.com/cedx/coveralls.php.git', $map->url);
    });
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $remote = (string) new GitRemote('origin', 'https://github.com/cedx/coveralls.php.git');

    $this->specify('should start with the class name', function() use ($remote) {
      $this->assertStringStartsWith('coveralls\GitRemote {', $remote);
    });

    $this->specify('should contain the instance properties', function() use ($remote) {
      $this->assertContains('"name":"origin"', $remote);
      $this->assertContains('"url":"https://github.com/cedx/coveralls.php.git"', $remote);
    });
  }
}
