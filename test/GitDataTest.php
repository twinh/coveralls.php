<?php declare(strict_types=1);
namespace Coveralls;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/** @testdox Coveralls\GitData */
class GitDataTest extends TestCase {

  /** @testdox ::fromJson() */
  function testFromJson(): void {
    it('should return an instance with default values for an empty map', function() {
      $data = GitData::fromJson(new \stdClass);
      expect($data->getBranch())->to->be->empty;
      expect($data->getCommit())->to->be->null;
      expect($data->getRemotes())->to->be->empty;
    });

    it('should return an initialized instance for a non-empty map', function() {
      $data = GitData::fromJson((object) [
        'branch' => 'develop',
        'head' => (object) ['id' => '2ef7bde608ce5404e97d5f042f95f89f1c232871'],
        'remotes' => [
          (object) ['name' => 'origin']
        ]
      ]);

      expect($data->getBranch())->to->equal('develop');

      /** @var GitCommit $commit */
      $commit = $data->getCommit();
      expect($commit->getId())->to->equal('2ef7bde608ce5404e97d5f042f95f89f1c232871');

      $remotes = $data->getRemotes();
      expect($remotes)->to->have->lengthOf(1);

      /** @var GitRemote $remote */
      $remote = $remotes[0];
      expect($remote)->to->be->an->instanceOf(GitRemote::class);
      expect($remote->getName())->to->equal('origin');
    });
  }

  /** @testdox ::fromRepository() */
  function testFromRepository(): void {
    it('should retrieve the Git data from the executable output', function() {
      $data = GitData::fromRepository();
      expect($data->getBranch())->to->not->be->empty;

      /** @var GitCommit $commit */
      $commit = $data->getCommit();
      expect($commit->getId())->to->match('/^[a-f\d]{40}$/');

      $remotes = $data->getRemotes();
      expect($remotes)->to->not->be->empty;
      expect($remotes[0])->to->be->an->instanceOf(GitRemote::class);

      /** @var GitRemote[] $origin */
      $origin = array_values(array_filter($remotes->getArrayCopy(), fn(GitRemote $remote) => $remote->getName() == 'origin'));
      expect($origin)->to->have->lengthOf(1);
      expect((string) $origin[0]->getUrl())->to->be->oneOf([
        'https://github.com/cedx/coveralls.php',
        'https://github.com/cedx/coveralls.php.git'
      ]);
    });
  }

  /** @testdox ->jsonSerialize() */
  function testJsonSerialize(): void {
    it('should return a map with default values for a newly created instance', function() {
      $map = (new GitData(new GitCommit('')))->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(3);
      expect($map->branch)->to->be->empty;
      expect($map->head)->to->be->an->instanceOf(\stdClass::class);
      expect($map->remotes)->to->be->empty;
    });

    it('should return a non-empty map for an initialized instance', function() {
      $map = (new GitData(new GitCommit('2ef7bde608ce5404e97d5f042f95f89f1c232871'), 'develop', [new GitRemote('origin')]))->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(3);
      expect($map->branch)->to->equal('develop');

      expect($map->head->id)->to->equal('2ef7bde608ce5404e97d5f042f95f89f1c232871');
      expect($map->remotes)->to->have->lengthOf(1);
      expect($map->remotes[0]->name)->to->equal('origin');
    });
  }
}
