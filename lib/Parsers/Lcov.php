<?php
declare(strict_types=1);
namespace Coveralls\Parsers;

use Coveralls\{Job, SourceFile};
use Lcov\{Record, Report};
use Webmozart\PathUtil\{Path};

/**
 * Parses [LCOV](http://ltp.sourceforge.net/coverage/lcov.php) coverage reports.
 */
abstract class Lcov {

  /**
   * Parses the specified coverage report.
   * @param string $report A coverage report in LCOV format.
   * @return Job The job corresponding to the specified coverage report.
   * @throws \RuntimeException A source file was not found.
   */
  static function parseReport(string $report): Job {
    $records = Report::fromCoverage($report)->getRecords()->getArrayCopy();
    $workingDir = getcwd() ?: '.';

    return new Job(array_map(function(Record $record) use ($workingDir) {
      $sourceFile = $record->getSourceFile();
      $source = (string)@file_get_contents($sourceFile);
      if (!mb_strlen($source)) {
        throw new \RuntimeException("Source file not found: $sourceFile");
      }

      $coverage = new \SplFixedArray(count(preg_split('/\r?\n/', $source) ?: []));
      if ($lines = $record->getLines()) {
        foreach ($lines->getData() as $lineData) {
          $coverage[$lineData->getLineNumber() - 1] = $lineData->getExecutionCount();
        }
      }

      $filename = Path::makeRelative($sourceFile, $workingDir);
      return new SourceFile($filename, md5($source), $source, $coverage->toArray());
    }, $records));
  }
}
