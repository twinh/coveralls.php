<?php
/**
 * Implementation of the `coveralls\test\GitDataTest` class.
 */
namespace coveralls\test;

use Codeception\{Specify};
use coveralls\{GitCommit, GitData, GitRemote};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \coveralls\GitData
 */
class GitDataTest extends TestCase {
  use Specify;

  /**
   * @test ::fromJSON
   */
  public function testFromJSON() {
    $this->specify('should return a null reference with a non-object value', function() {
      $this->assertNull(GitData::fromJSON('foo'));
    });

    $this->specify('should return an instance with default values for an empty map', function() {
      $data = GitData::fromJSON([]);
      $this->assertInstanceOf(GitData::class, $data);
      $this->assertEmpty($data->getBranch());
      $this->assertNull($data->getCommit());
      $this->assertCount(0, $data->getRemotes());
    });

    $this->specify('should return an initialized instance for a non-empty map', function() {
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
    });
  }

  /**
   * @test ::fromRepository
   */
  public function testFromRepository() {
    $this->specify('should retrieve the Git data from the executable output', function() {
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
    });
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    $this->specify('should return a map with default values for a newly created instance', function() {
      $map = (new GitData())->jsonSerialize();
      $this->assertCount(3, get_object_vars($map));
      $this->assertEmpty($map->branch);
      $this->assertNull($map->head);
      $this->assertCount(0, $map->remotes);
    });

    $this->specify('should return a non-empty map for an initialized instance', function() {
      $map = (new GitData(new GitCommit('2ef7bde608ce5404e97d5f042f95f89f1c232871'), 'develop', [new GitRemote('origin')]))->jsonSerialize();
      $this->assertCount(3, get_object_vars($map));
      $this->assertEquals('develop', $map->branch);

      $this->assertInstanceOf(\stdClass::class, $map->head);
      $this->assertEquals('2ef7bde608ce5404e97d5f042f95f89f1c232871', $map->head->id);

      $this->assertCount(1, $map->remotes);
      $this->assertInstanceOf(\stdClass::class, $map->remotes[0]);
      $this->assertEquals('origin', $map->remotes[0]->name);
    });
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $data = (string) new GitData(null, 'develop');

    $this->specify('should start with the class name', function() use ($data) {
      $this->assertStringStartsWith('coveralls\GitData {', $data);
    });

    $this->specify('should contain the instance properties', function() use ($data) {
      $this->assertContains('"branch":"develop"', $data);
      $this->assertContains('"head":null', $data);
      $this->assertContains('"remotes":[]', $data);
    });
  }
}
