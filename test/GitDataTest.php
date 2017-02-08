<?php
/**
 * Implementation of the `coveralls\test\GitDataTest` class.
 */
namespace coveralls\test;
use coveralls\{GitCommit, GitData, GitRemote};

/**
 * Tests the features of the `coveralls\GitData` class.
 */
class GitDataTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the `GitData::fromJSON()` method.
   */
  public function testFromJSON() {
    $this->assertNull(GitData::fromJSON('foo'));

    $data = GitData::fromJSON([]);
    $this->assertInstanceOf(GitData::class, $data);
    $this->assertEmpty($data->getBranch());
    $this->assertNull($data->getCommit());
    $this->assertCount(0, $data->getRemotes());

    $data = GitData::fromJSON([
      'branch' => 'develop',
      'head' => ['id' => '2ef7bde608ce5404e97d5f042f95f89f1c232871'],
      'remotes' => [
        ['name' => 'origin']
      ]
    ]);

    $this->assertInstanceOf(GitData::class, $data);
    $this->assertEquals('develop', $data->getBranch());

    $commit = $data->getCommit();
    $this->assertInstanceOf(GitCommit::class, $commit);
    $this->assertEquals('2ef7bde608ce5404e97d5f042f95f89f1c232871', $commit->getId());

    $remotes = $data->getRemotes();
    $this->assertCount(1, $remotes);
    $this->assertInstanceOf(GitRemote::class, $remotes[0]);
    $this->assertEquals('origin', $remotes[0]->getName());
  }

  /**
   * Tests the `GitData::fromRepository()` method.
   */
  public function testFromRepository() {
    $data = GitData::fromRepository(__DIR__.'/..');
    $this->assertNotEmpty($data->getBranch());

    $commit = $data->getCommit();
    $this->assertInstanceOf(GitCommit::class, $commit);
    $this->assertRegExp('/^[a-f\d]{40}$/', $commit->getId());

    $remotes = $data->getRemotes();
    $this->assertGreaterThanOrEqual(1, count($remotes));
    $this->assertInstanceOf(GitRemote::class, $remotes[0]);

    $origin = array_filter($remotes->getArrayCopy(), function(GitRemote $remote) {
      return $remote->getName() == 'origin';
    });

    $this->assertCount(1, $origin);
    $this->assertEquals('https://github.com/cedx/coveralls.php.git', $origin[0]->getURL());
  }

  /**
   * Tests the `GitData::jsonSerialize()` method.
   */
  public function testJsonSerialize() {
    $map = (new GitData())->jsonSerialize();
    $this->assertCount(3, get_object_vars($map));
    $this->assertEmpty($map->branch);
    $this->assertNull($map->head);
    $this->assertCount(0, $map->remotes);

    $map = (new GitData(new GitCommit('2ef7bde608ce5404e97d5f042f95f89f1c232871'), 'develop', [new GitRemote('origin')]))->jsonSerialize();
    $this->assertCount(3, get_object_vars($map));
    $this->assertEquals('develop', $map->branch);

    $this->assertInstanceOf(\stdClass::class, $map->head);
    $this->assertEquals('2ef7bde608ce5404e97d5f042f95f89f1c232871', $map->head->id);

    $this->assertCount(1, $map->remotes);
    $this->assertInstanceOf(\stdClass::class, $map->remotes[0]);
    $this->assertEquals('origin', $map->remotes[0]->name);
  }
}
