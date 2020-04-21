<?php declare(strict_types=1);
namespace Coveralls\Parsers;

use Coveralls\{SourceFile};
use PHPUnit\Framework\{TestCase};
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isInstanceOf, logicalNot};

/** @testdox Coveralls\Parsers\Clover */
class CloverTest extends TestCase {

  /** @testdox ::parseReport() */
  function testParseReport(): void {
    $report = new \SplFileObject('test/fixtures/clover.xml');

    // It should properly parse Clover reports.
    $job = Clover::parseReport((string) $report->fread($report->getSize()));
    $files = $job->getSourceFiles();
    assertThat($files, countOf(3));

    /** @var SourceFile $file */
    $file = $files[0];
    $subset = [null, 2, 2, 2, 2, null];
    assertThat($file, isInstanceOf(SourceFile::class));
    assertThat($file->getBranches(), isEmpty());
    assertThat(array_intersect($subset, (array) $file->getCoverage()), equalTo($subset));
    assertThat($file->getName(), equalTo(str_replace('/', DIRECTORY_SEPARATOR, 'src/Client.php')));
    assertThat($file->getSourceDigest(), logicalNot(isEmpty()));

    /** @var SourceFile $file */
    $file = $files[1];
    $subset = [null, 4, 4, 2, 2, 4, 2, 2, 4, 4, null];
    assertThat($file, isInstanceOf(SourceFile::class));
    assertThat($file->getBranches(), isEmpty());
    assertThat(array_intersect($subset, (array) $file->getCoverage()), equalTo($subset));
    assertThat($file->getName(), equalTo(str_replace('/', DIRECTORY_SEPARATOR, 'src/Configuration.php')));
    assertThat($file->getSourceDigest(), logicalNot(isEmpty()));

    /** @var SourceFile $file */
    $file = $files[2];
    $subset = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
    assertThat($file, isInstanceOf(SourceFile::class));
    assertThat($file->getBranches(), isEmpty());
    assertThat(array_intersect($subset, (array) $file->getCoverage()), equalTo($subset));
    assertThat($file->getName(), equalTo(str_replace('/', DIRECTORY_SEPARATOR, 'src/GitCommit.php')));
    assertThat($file->getSourceDigest(), logicalNot(isEmpty()));

    // It should throw an exception if the Clover report is invalid or empty.
    $this->expectException(\InvalidArgumentException::class);
    Clover::parseReport('<coverage><foo/></coverage>');
  }
}
