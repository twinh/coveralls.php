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

      /** @var SourceFile $file */
      $file = $files[0];
      $coverage = [null, 2, 2, 2, 2, null];
      expect($file)->to->be->an->instanceOf(SourceFile::class);
      expect($file->getName())->to->equal(str_replace('/', DIRECTORY_SEPARATOR, 'lib/Client.php'));
      expect($file->getSourceDigest())->to->not->be->empty;
      expect($file->getBranches()->getArrayCopy())->to->be->empty;
      expect(array_intersect($coverage, $file->getCoverage()->getArrayCopy()))->to->equal($coverage);

      /** @var SourceFile $file */
      $file = $files[1];
      $branches = [8, 0, 0, 2, 8, 0, 1, 2, 11, 0, 0, 2, 11, 0, 1, 2];
      $coverage = [null, 4, 4, 2, 2, 4, 2, 2, 4, 4, null];
      expect($file->getName())->to->equal(str_replace('/', DIRECTORY_SEPARATOR, 'lib/Configuration.php'));
      expect($file->getSourceDigest())->to->not->be->empty;
      expect(array_intersect($branches, $file->getBranches()->getArrayCopy()))->to->equal($branches);
      expect(array_intersect($coverage, $file->getCoverage()->getArrayCopy()))->to->equal($coverage);

      /** @var SourceFile $file */
      $file = $files[2];
      $branches = [8, 0, 0, 2, 8, 0, 1, 0, 11, 0, 0, 0, 11, 0, 1, 2];
      $coverage = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
      expect($file->getName())->to->equal(str_replace('/', DIRECTORY_SEPARATOR, 'lib/GitCommit.php'));
      expect($file->getSourceDigest())->to->not->be->empty;
      expect(array_intersect($branches, $file->getBranches()->getArrayCopy()))->to->equal($branches);
      expect(array_intersect($coverage, $file->getCoverage()->getArrayCopy()))->to->equal($coverage);
    });

    it('should throw an exception when parsing reports with invalid source file', function() {
      expect(fn() => Lcov::parseReport((string) @file_get_contents('test/fixtures/invalid_lcov.info')))->to->throw(\RuntimeException::class);
    });
  }
}
