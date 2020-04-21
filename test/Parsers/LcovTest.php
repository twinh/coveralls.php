<?php declare(strict_types=1);
namespace Coveralls\Parsers;

use Coveralls\{SourceFile};
use PHPUnit\Framework\{TestCase};
use function PHPUnit\Framework\{assertThat, countOf, equalTo, isEmpty, isInstanceOf, logicalNot};

/** @testdox Coveralls\Parsers\Lcov */
class LcovTest extends TestCase {

  /** @testdox ::parseReport() */
  function testParseReport(): void {
    $report = new \SplFileObject('test/fixtures/lcov.info');

    // It should properly parse LCOV reports.
    $job = Lcov::parseReport((string) $report->fread($report->getSize()));
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
    assertThat((array) $file->getBranches(), equalTo([8, 0, 0, 2, 8, 0, 1, 2, 11, 0, 0, 2, 11, 0, 1, 2]));
    assertThat(array_intersect($subset, (array) $file->getCoverage()), equalTo($subset));
    assertThat($file->getName(), equalTo(str_replace('/', DIRECTORY_SEPARATOR, 'src/Configuration.php')));
    assertThat($file->getSourceDigest(), logicalNot(isEmpty()));

    /** @var SourceFile $file */
    $file = $files[2];
    $subset = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
    assertThat((array) $file->getBranches(), equalTo([8, 0, 0, 2, 8, 0, 1, 0, 11, 0, 0, 0, 11, 0, 1, 2]));
    assertThat(array_intersect($subset, (array) $file->getCoverage()), equalTo($subset));
    assertThat($file->getName(), equalTo(str_replace('/', DIRECTORY_SEPARATOR, 'src/GitCommit.php')));
    assertThat($file->getSourceDigest(), logicalNot(isEmpty()));

    // It should throw an exception when parsing reports with invalid source file.
    $this->expectException(\RuntimeException::class);
    $report = new \SplFileObject('test/fixtures/invalid_lcov.info');
    Lcov::parseReport((string) $report->fread($report->getSize()));
  }
}
