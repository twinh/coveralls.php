<?php
declare(strict_types=1);
namespace Coveralls\Parsers\Clover;

use Coveralls\{SourceFile};
use PHPUnit\Framework\{TestCase};

/**
 * Tests the features of the `Coveralls\Parsers\Clover\parseReport` function.
 */
class CloverTest extends TestCase {

  /**
   * Performs a common set of tasks just before the first test method is called.
   */
  static function setUpBeforeClass(): void {
    require_once __DIR__.'/../../lib/Parsers/Clover.php';
  }

  /**
   * @test parseReport
   */
  function testParseReport(): void {
    // It should properly parse Clover reports.
    /** @var \Coveralls\Job $job */
    $job = parseReport(file_get_contents('test/fixtures/clover.xml'));
    $files = $job->getSourceFiles();
    assertThat($files, countOf(3));

    $subset = [null, 2, 2, 2, 2, null];
    assertThat($files[0], isInstanceOf(SourceFile::class));
    assertThat($files[0]->getName(), equalTo('lib/Client.php'));
    assertThat($files[0]->getSourceDigest(), logicalNot(isEmpty()));
    assertThat(array_intersect($subset, $files[0]->getCoverage()->getArrayCopy()), equalTo($subset));

    $subset = [null, 4, 4, 2, 2, 4, 2, 2, 4, 4, null];
    assertThat($files[1]->getName(), equalTo('lib/Configuration.php'));
    assertThat($files[1]->getSourceDigest(), logicalNot(isEmpty()));
    assertThat(array_intersect($subset, $files[1]->getCoverage()->getArrayCopy()), equalTo($subset));

    $subset = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
    assertThat($files[2]->getName(), equalTo('lib/GitCommit.php'));
    assertThat($files[2]->getSourceDigest(), logicalNot(isEmpty()));
    assertThat(array_intersect($subset, $files[2]->getCoverage()->getArrayCopy()), equalTo($subset));

    // It should throw an exception if the Clover report is invalid or empty.
    try {
      parseReport('<coverage><foo/></coverage>');
      $this->fail('Exception not thrown.');
    }

    catch (\Throwable $e) {
      assertThat($e, isInstanceOf(\InvalidArgumentException::class));
    }
  }
}
