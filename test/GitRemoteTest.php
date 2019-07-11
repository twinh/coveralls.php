<?php declare(strict_types=1);
namespace Coveralls;

use function PHPUnit\Expect\{expect, it};
use GuzzleHttp\Psr7\{Uri};
use PHPUnit\Framework\{TestCase};

/** Tests the features of the `Coveralls\GitRemote` class. */
class GitRemoteTest extends TestCase {

  /** @test GitRemote::fromJson() */
  function testFromJson(): void {
    it('should return an instance with default values for an empty map', function() {
      $remote = GitRemote::fromJson(new \stdClass);
      expect($remote->getName())->to->be->empty;
      expect($remote->getUrl())->to->be->null;
    });

    it('should return an initialized instance for a non-empty map', function() {
      $remote = GitRemote::fromJson((object) ['name' => 'origin', 'url' => 'git@github.com:cedx/coveralls.php.git']);
      expect($remote->getName())->to->equal('origin');
      expect((string) $remote->getUrl())->to->equal('ssh://git@github.com/cedx/coveralls.php.git');

      $remote = GitRemote::fromJson((object) ['name' => 'origin', 'url' => 'https://github.com/cedx/coveralls.php.git']);
      expect((string) $remote->getUrl())->to->equal('https://github.com/cedx/coveralls.php.git');
    });
  }

  /** @test GitRemote->jsonSerialize() */
  function testJsonSerialize(): void {
    it('should return a map with default values for a newly created instance', function() {
      $map = (new GitRemote(''))->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(2);
      expect($map->name)->to->be->empty;
      expect($map->url)->to->be->null;
    });

    it('should return a non-empty map for an initialized instance', function() {
      $map = (new GitRemote('origin', 'git@github.com:cedx/coveralls.php.git'))->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(2);
      expect($map->name)->to->equal('origin');
      expect($map->url)->to->equal('ssh://git@github.com/cedx/coveralls.php.git');

      $map = (new GitRemote('origin', new Uri('https://github.com/cedx/coveralls.php.git')))->jsonSerialize();
      expect($map->url)->to->equal('https://github.com/cedx/coveralls.php.git');
    });
  }
}
