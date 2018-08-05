<?php
declare(strict_types=1);
namespace Coveralls;

use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `Coveralls\GitRemote` class.
 */
class GitRemoteTest extends TestCase {

  /**
   * @test GitRemote::fromJson
   */
  public function testFromJson(): void {
    // It should return a null reference with a non-object value.
    assertThat(GitRemote::fromJson('foo'), isNull());

    // It should return an instance with default values for an empty map.
    $remote = GitRemote::fromJson([]);
    assertThat($remote, isInstanceOf(GitRemote::class));
    assertThat($remote->getName(), isEmpty());
    assertThat($remote->getUrl(), isNull());

    // It should return an initialized instance for a non-empty map.
    $remote = GitRemote::fromJson(['name' => 'origin', 'url' => 'https://github.com/cedx/coveralls.php.git']);
    assertThat($remote, isInstanceOf(GitRemote::class));
    assertThat($remote->getName(), equalTo('origin'));
    assertThat((string) $remote->getUrl(), equalTo('https://github.com/cedx/coveralls.php.git'));
  }

  /**
   * @test GitRemote::jsonSerialize
   */
  public function testJsonSerialize(): void {
    // It should return a map with default values for a newly created instance.
    $map = (new GitRemote(''))->jsonSerialize();
    assertThat(get_object_vars($map), countOf(2));
    assertThat($map->name, isEmpty());
    assertThat($map->url, isNull());

    // It should return a non-empty map for an initialized instance.
    $map = (new GitRemote('origin', 'https://github.com/cedx/coveralls.php.git'))->jsonSerialize();
    assertThat(get_object_vars($map), countOf(2));
    assertThat($map->name, equalTo('origin'));
    assertThat($map->url, equalTo('https://github.com/cedx/coveralls.php.git'));
  }

  /**
   * @test GitRemote::__toString
   */
  public function testToString(): void {
    $remote = (string) new GitRemote('origin', 'https://github.com/cedx/coveralls.php.git');

    // It should start with the class name.
    assertThat($remote, stringStartsWith('Coveralls\GitRemote {'));

    // It should contain the instance properties.
    assertThat($remote, logicalAnd(stringContains('"name":"origin"'), stringContains('"url":"https://github.com/cedx/coveralls.php.git"')));
  }
}
