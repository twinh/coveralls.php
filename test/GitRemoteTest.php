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
   * @covers ::fromJSON
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
   * @covers ::jsonSerialize
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

  /**
   * @covers ::__toString
   */
  public function testToString() {
    $remote = (string) new GitRemote('origin', 'https://github.com/cedx/coveralls.php.git');
    $this->assertStringStartsWith('coveralls\GitRemote {', $remote);
    $this->assertContains('"name":"origin"', $remote);
    $this->assertContains('"url":"https://github.com/cedx/coveralls.php.git"', $remote);
  }
}
