<?php declare(strict_types=1);
namespace Coveralls\Parsers;

use function PHPUnit\Expect\{expect, it};
use Coveralls\{SourceFile};
use PHPUnit\Framework\{TestCase};

/** Tests the features of the `Coveralls\Parsers\Clover` class. */
class CloverTest extends TestCase {

  /** @test Clover::parseReport() */
  function testParseReport(): void {
    it('should properly parse Clover reports', function() {
      $job = Clover::parseReport((string) @file_get_contents('test/fixtures/clover.xml'));
      $files = $job->getSourceFiles();
      expect($files)->to->have->lengthOf(3);

      $subset = [null, 2, 2, 2, 2, null];
      expect($files[0])->to->be->an->instanceOf(SourceFile::class);
      expect($files[0]->getName())->to->equal('lib/Http/Client.php');
      expect($files[0]->getSourceDigest())->to->not->be->empty;
      expect(array_intersect($subset, $files[0]->getCoverage()->getArrayCopy()))->to->equal($subset);

      $subset = [null, 4, 4, 2, 2, 4, 2, 2, 4, 4, null];
      expect($files[1]->getName())->to->equal('lib/Configuration.php');
      expect($files[1]->getSourceDigest())->to->not->be->empty;
      expect(array_intersect($subset, $files[1]->getCoverage()->getArrayCopy()))->to->equal($subset);

      $subset = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
      expect($files[2]->getName())->to->equal('lib/GitCommit.php');
      expect($files[2]->getSourceDigest())->to->not->be->empty;
      expect(array_intersect($subset, $files[2]->getCoverage()->getArrayCopy()))->to->equal($subset);
    });

    it('should throw an exception if the Clover report is invalid or empty', function() {
      expect(function() { Clover::parseReport('<coverage><foo/></coverage>'); })->to->throw(\InvalidArgumentException::class);
    });
  }
}
