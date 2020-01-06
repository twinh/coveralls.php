<?php declare(strict_types=1);
namespace Coveralls\Parsers;

use function PHPUnit\Expect\{expect, it};
use Coveralls\{SourceFile};
use PHPUnit\Framework\{TestCase};

/** @testdox Coveralls\Parsers\Clover */
class CloverTest extends TestCase {

  /** @testdox ::parseReport() */
  function testParseReport(): void {
    it('should properly parse Clover reports', function() {
      $job = Clover::parseReport((string) @file_get_contents('test/fixtures/clover.xml'));
      $files = $job->getSourceFiles();
      expect($files)->to->have->lengthOf(3);

      /** @var SourceFile $file */
      $file = $files[0];
      $subset = [null, 2, 2, 2, 2, null];
      expect($file)->to->be->an->instanceOf(SourceFile::class);
      expect($file->getName())->to->equal('lib/Http/Client.php');
      expect($file->getSourceDigest())->to->not->be->empty;
      expect(array_intersect($subset, $file->getCoverage()->getArrayCopy()))->to->equal($subset);

      /** @var SourceFile $file */
      $file = $files[1];
      $subset = [null, 4, 4, 2, 2, 4, 2, 2, 4, 4, null];
      expect($file->getName())->to->equal('lib/Configuration.php');
      expect($file->getSourceDigest())->to->not->be->empty;
      expect(array_intersect($subset, $file->getCoverage()->getArrayCopy()))->to->equal($subset);

      /** @var SourceFile $file */
      $file = $files[2];
      $subset = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
      expect($file->getName())->to->equal('lib/GitCommit.php');
      expect($file->getSourceDigest())->to->not->be->empty;
      expect(array_intersect($subset, $file->getCoverage()->getArrayCopy()))->to->equal($subset);
    });

    it('should throw an exception if the Clover report is invalid or empty', function() {
      expect(fn() => Clover::parseReport('<coverage><foo/></coverage>'))->to->throw(\InvalidArgumentException::class);
    });
  }
}
