<?php declare(strict_types=1);
namespace Coveralls;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/** Tests the features of the `Coveralls\Job` class. */
class JobTest extends TestCase {

  /** @test Job::fromJson() */
  function testFromJson(): void {
    it('should return an instance with default values for an empty map', function() {
      $job = Job::fromJson(new \stdClass);
      expect($job->getGit())->to->be->null;
      expect($job->isParallel())->to->be->false;
      expect($job->getRepoToken())->to->be->empty;
      expect($job->getRunAt())->to->be->null;
      expect($job->getSourceFiles())->to->be->empty;
    });

    it('should return an initialized instance for a non-empty map', function() {
      $job = Job::fromJson((object) [
        'git' => (object) ['branch' => 'develop'],
        'parallel' => true,
        'repo_token' => 'yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt',
        'run_at' => '2017-01-29T03:43:30+01:00',
        'source_files' => [
          (object) ['name' => '/home/cedx/coveralls.php']
        ]
      ]);

      expect($job->isParallel())->to->be->true;
      expect($job->getRepoToken())->to->equal('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt');

      /** @var GitData $git */
      $git = $job->getGit();
      expect($git->getBranch())->to->equal('develop');

      /** @var \DateTime $runAt */
      $runAt = $job->getRunAt();
      expect($runAt->format('c'))->to->equal('2017-01-29T03:43:30+01:00');

      $sourceFiles = $job->getSourceFiles();
      expect($sourceFiles)->to->have->lengthOf(1);
      expect($sourceFiles[0])->to->be->an->instanceOf(SourceFile::class);
      expect($sourceFiles[0]->getName())->to->equal('/home/cedx/coveralls.php');
    });
  }

  /** @test Job->jsonSerialize() */
  function testJsonSerialize(): void {
    it('should return a map with default values for a newly created instance', function() {
      $map = (new Job)->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(1);
      expect($map->source_files)->to->be->empty;
    });

    it('should return a non-empty map for an initialized instance', function() {
      $map = (new Job([new SourceFile('/home/cedx/coveralls.php', '')]))
        ->setGit(new GitData(new GitCommit(''), 'develop'))
        ->setParallel(true)
        ->setRepoToken('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt')
        ->setRunAt(new \DateTime('2017-01-29T03:43:30+01:00'))
        ->jsonSerialize();

      expect(get_object_vars($map))->to->have->lengthOf(5);
      expect($map->parallel)->to->be->true;
      expect($map->repo_token)->to->equal('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt');
      expect($map->run_at)->to->equal('2017-01-29T03:43:30+01:00');

      expect($map->git->branch)->to->equal('develop');
      expect($map->source_files)->to->have->lengthOf(1);
      expect($map->source_files[0]->name)->to->equal('/home/cedx/coveralls.php');
    });
  }
}
