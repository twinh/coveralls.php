<?php declare(strict_types=1);
namespace Coveralls\Parsers;

use function PHPUnit\Expect\{expect, it};
use Coveralls\{SourceFile};
use PHPUnit\Framework\{TestCase};

/** @testdox Coveralls\Parsers\Lcov */
class LcovTest extends TestCase {

  /** @testdox ::parseReport() */
  function testParseReport(): void {
    it('should properly parse LCOV reports', function() {
      $job = Lcov::parseReport((string) @file_get_contents('test/fixtures/lcov.info'));
      $files = $job->getSourceFiles();
      expect($files)->to->have->lengthOf(3);

      $coverage = [null, 2, 2, 2, 2, null];
      expect($files[0])->to->be->an->instanceOf(SourceFile::class);
      expect($files[0]->getName())->to->equal('lib/Http/Client.php');
      expect($files[0]->getSourceDigest())->to->not->be->empty;
      expect($files[0]->getBranches()->getArrayCopy())->to->be->empty;
      expect(array_intersect($coverage, $files[0]->getCoverage()->getArrayCopy()))->to->equal($coverage);

      $branches = [8, 0, 0, 2, 8, 0, 1, 2, 11, 0, 0, 2, 11, 0, 1, 2];
      $coverage = [null, 4, 4, 2, 2, 4, 2, 2, 4, 4, null];
      expect($files[1]->getName())->to->equal('lib/Configuration.php');
      expect($files[1]->getSourceDigest())->to->not->be->empty;
      expect(array_intersect($branches, $files[1]->getBranches()->getArrayCopy()))->to->equal($branches);
      expect(array_intersect($coverage, $files[1]->getCoverage()->getArrayCopy()))->to->equal($coverage);

      $branches = [8, 0, 0, 2, 8, 0, 1, 0, 11, 0, 0, 0, 11, 0, 1, 2];
      $coverage = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
      expect($files[2]->getName())->to->equal('lib/GitCommit.php');
      expect($files[2]->getSourceDigest())->to->not->be->empty;
      expect(array_intersect($branches, $files[2]->getBranches()->getArrayCopy()))->to->equal($branches);
      expect(array_intersect($coverage, $files[2]->getCoverage()->getArrayCopy()))->to->equal($coverage);
    });

    it('should throw an exception when parsing reports with invalid source file', function() {
      expect(fn() => Lcov::parseReport((string) @file_get_contents('test/fixtures/invalid_lcov.info')))->to->throw(\RuntimeException::class);
    });
  }
}
