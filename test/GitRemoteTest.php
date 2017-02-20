<?php
/**
 * Implementation of the `coveralls\test\GitRemoteTest` class.
 */
namespace coveralls\test;

use coveralls\{GitRemote};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \coveralls\GitRemote
 */
class GitRemoteTest extends TestCase {

  /**
   * @test ::fromJSON
   */
  public function testFromJSON() {
    // Should return a null reference with a non-object value.
    $this->assertNull(GitRemote::fromJSON('foo'));

    // Should return an instance with default values for an empty map.
    $remote = GitRemote::fromJSON([]);
    $this->assertInstanceOf(GitRemote::class, $remote);
    $this->assertEmpty($remote->getName());
    $this->assertEmpty($remote->getURL());

    // Should return an initialized instance for a non-empty map.
    $remote = GitRemote::fromJSON(['name' => 'origin', 'url' => 'https://github.com/cedx/coveralls.php.git']);
    $this->assertInstanceOf(GitRemote::class, $remote);
    $this->assertEquals('origin', $remote->getName());
    $this->assertEquals('https://github.com/cedx/coveralls.php.git', $remote->getURL());
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    // Should return a map with default values for a newly created instance.
    $map = (new GitRemote())->jsonSerialize();
    $this->assertCount(2, get_object_vars($map));
    $this->assertEmpty($map->name);
    $this->assertEmpty($map->url);

    // Should return a non-empty map for an initialized instance.
    $map = (new GitRemote('origin', 'https://github.com/cedx/coveralls.php.git'))->jsonSerialize();
    $this->assertCount(2, get_object_vars($map));
    $this->assertEquals('origin', $map->name);
    $this->assertEquals('https://github.com/cedx/coveralls.php.git', $map->url);
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $remote = (string) new GitRemote('origin', 'https://github.com/cedx/coveralls.php.git');

    // Should start with the class name.
    $this->assertStringStartsWith('coveralls\GitRemote {', $remote);

    // Should contain the instance properties.
    $this->assertContains('"name":"origin"', $remote);
    $this->assertContains('"url":"https://github.com/cedx/coveralls.php.git"', $remote);
  }
}
