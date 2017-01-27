<?php
/**
 * Implementation of the `coveralls\test\GitRemoteTest` class.
 */
namespace coveralls\test;
use coveralls\{GitRemote};

/**
 * Tests the features of the `coveralls\GitRemote` class.
 */
class GitRemoteTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the `GitRemote::fromJSON()` method.
   */
  public function testFromJSON() {
    $this->assertNull(GitRemote::fromJSON('foo'));

    $remote = GitRemote::fromJSON([]);
    $this->assertInstanceOf(GitRemote::class, $remote);
    $this->assertEmpty($remote->getName());
    $this->assertEmpty($remote->getURL());

    $remote = GitRemote::fromJSON(['name' => 'origin', 'url' => 'https://github.com/cedx/coveralls.php.git']);
    $this->assertInstanceOf(GitRemote::class, $remote);
    $this->assertEquals('origin', $remote->getName());
    $this->assertEquals('https://github.com/cedx/coveralls.php.git', $remote->getURL());
  }

  /**
   * Tests the `GitRemote::jsonSerialize()` method.
   */
  public function testJsonSerialize() {
    $map = (new GitRemote())->jsonSerialize();
    $this->assertCount(2, get_object_vars($map));
    $this->assertEmpty($map->name);
    $this->assertEmpty($map->url);

    $map = (new GitRemote('origin', 'https://github.com/cedx/coveralls.php.git'))->jsonSerialize();
    $this->assertCount(2, get_object_vars($map));
    $this->assertEquals('origin', $map->name);
    $this->assertEquals('https://github.com/cedx/coveralls.php.git', $map->url);
  }
}
