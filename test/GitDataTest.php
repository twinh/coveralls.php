<?php
namespace coveralls;
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `coveralls\GitData` class.
 */
class GitDataTest extends TestCase {

  /**
   * @test GitData::fromJSON
   */
  public function testFromJSON() {
    it('should return a null reference with a non-object value', function() {
      expect(GitData::fromJSON('foo'))->to->be->null;
    });

    it('should return an instance with default values for an empty map', function() {
      $data = GitData::fromJSON([]);
      expect($data)->to->be->instanceOf(GitData::class);
      expect($data->getBranch())->to->be->empty;
      expect($data->getCommit())->to->be->null;
      expect($data->getRemotes())->to->be->empty;
    });

    it('should return an initialized instance for a non-empty map', function() {
      $data = GitData::fromJSON([
        'branch' => 'develop',
        'head' => ['id' => '2ef7bde608ce5404e97d5f042f95f89f1c232871'],
        'remotes' => [
          ['name' => 'origin']
        ]
      ]);

      expect($data)->to->be->instanceOf(GitData::class);
      expect($data->getBranch())->to->equal('develop');

      $commit = $data->getCommit();
      expect($commit)->to->be->instanceOf(GitCommit::class);
      expect($commit->getId())->to->equal('2ef7bde608ce5404e97d5f042f95f89f1c232871');

      $remotes = $data->getRemotes();
      expect($remotes)->to->have->lengthOf(1);
      expect($remotes[0])->to->be->instanceOf(GitRemote::class);
      expect($remotes[0]->getName())->to->equal('origin');
    });
  }

  /**
   * @test GitData::fromRepository
   */
  public function testFromRepository() {
    it('should retrieve the Git data from the executable output', function() {
      $data = GitData::fromRepository(__DIR__.'/..');
      expect($data->getBranch())->to->not->be->empty;

      $commit = $data->getCommit();
      expect($commit)->to->be->instanceOf(GitCommit::class);
      expect($commit->getId())->to->match('/^[a-f\d]{40}$/');

      $remotes = $data->getRemotes();
      expect($remotes)->to->not->be->empty;
      expect($remotes[0])->to->be->instanceOf(GitRemote::class);

      /** @var GitRemote[] $origin */
      $origin = array_filter($remotes->getArrayCopy(), function(GitRemote $remote) {
        return $remote->getName() == 'origin';
      });

      expect($origin)->to->have->lengthOf(1);
      expect($origin[0]->getURL())->to->equal('https://github.com/cedx/coveralls.php.git');
    });
  }

  /**
   * @test GitData::jsonSerialize
   */
  public function testJsonSerialize() {
    it('should return a map with default values for a newly created instance', function() {
      $map = (new GitData())->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(3);
      expect($map->branch)->to->be->empty;
      expect($map->head)->to->be->null;
      expect($map->remotes)->to->be->an('array')->and->be->empty;
    });

    it('should return a non-empty map for an initialized instance', function() {
      $map = (new GitData(new GitCommit('2ef7bde608ce5404e97d5f042f95f89f1c232871'), 'develop', [new GitRemote('origin')]))->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(3);
      expect($map->branch)->to->equal('develop');

      expect($map->head)->to->be->an('object');
      expect($map->head->id)->to->equal('2ef7bde608ce5404e97d5f042f95f89f1c232871');

      expect($map->remotes)->to->be->an('array')->and->have->lengthOf(1);
      expect($map->remotes[0])->to->be->an('object');
      expect($map->remotes[0]->name)->to->equal('origin');
    });
  }

  /**
   * @test GitData::__toString
   */
  public function testToString() {
    $data = (string) new GitData(new GitCommit('2ef7bde608ce5404e97d5f042f95f89f1c232871'), 'develop', [new GitRemote('origin')]);

    it('should start with the class name', function() use ($data) {
      expect($data)->startWith('coveralls\GitData {');
    });

    it('should contain the instance properties', function() use ($data) {
      expect($data)->to->contain('"branch":"develop"')
        ->and->contain('"head":{')
        ->and->contain('"remotes":[{');
    });
  }
}
