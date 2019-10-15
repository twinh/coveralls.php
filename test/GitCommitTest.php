<?php declare(strict_types=1);
namespace Coveralls;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/** @testdox Coveralls\GitCommit */
class GitCommitTest extends TestCase {

  /** @testdox ::fromJson() */
  function testFromJson(): void {
    it('should return an instance with default values for an empty map', function() {
      $commit = GitCommit::fromJson(new \stdClass);
      expect($commit->getAuthorEmail())->to->be->empty;
      expect($commit->getAuthorName())->to->be->empty;
      expect($commit->getId())->to->be->empty;
      expect($commit->getMessage())->to->be->empty;
    });

    it('should return an initialized instance for a non-empty map', function() {
      $commit = GitCommit::fromJson((object) [
        'author_email' => 'anonymous@secret.com',
        'author_name' => 'Anonymous',
        'id' => '2ef7bde608ce5404e97d5f042f95f89f1c232871',
        'message' => 'Hello World!'
      ]);

      expect($commit->getAuthorEmail())->to->equal('anonymous@secret.com');
      expect($commit->getAuthorName())->to->equal('Anonymous');
      expect($commit->getId())->to->equal('2ef7bde608ce5404e97d5f042f95f89f1c232871');
      expect($commit->getMessage())->to->equal('Hello World!');
    });
  }

  /** @testdox ->jsonSerialize() */
  function testJsonSerialize(): void {
    it('should return a map with default values for a newly created instance', function() {
      $map = (new GitCommit(''))->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(1);
      expect($map->id)->to->be->empty;
    });

    it('should return a non-empty map for an initialized instance', function() {
      $map = (new GitCommit('2ef7bde608ce5404e97d5f042f95f89f1c232871', 'Hello World!'))
        ->setAuthorEmail('anonymous@secret.com')
        ->setAuthorName('Anonymous')
        ->jsonSerialize();

      expect(get_object_vars($map))->to->have->lengthOf(4);
      expect($map->author_email)->to->equal('anonymous@secret.com');
      expect($map->author_name)->to->equal('Anonymous');
      expect($map->id)->to->equal('2ef7bde608ce5404e97d5f042f95f89f1c232871');
      expect($map->message)->to->equal('Hello World!');
    });
  }
}
