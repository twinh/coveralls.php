<?php
declare(strict_types=1);
namespace Coveralls\Parsers\Clover;

use Coveralls\{Job, SourceFile};
use Webmozart\PathUtil\{Path};

/**
 * Parses the specified [Clover](https://www.atlassian.com/software/clover) coverage report.
 * @param string $report A coverage report in LCOV format.
 * @return Job The job corresponding to the specified coverage report.
 * @throws \InvalidArgumentException The specified Clover report has an invalid format.
 * @throws \RuntimeException A source file was not found.
 */
function parseReport(string $report): Job {
  $xml = @simplexml_load_string($report);
  if (!$xml || !$xml->count() || !$xml->project->count())
    throw new \InvalidArgumentException('The specified Clover report is invalid.');

  $files = array_merge($xml->xpath('/coverage/project/file') ?: [], $xml->xpath('/coverage/project/package/file') ?: []);
  $workingDir = getcwd() ?: '.';

  return new Job(array_map(function(\SimpleXMLElement $file) use ($workingDir) {
    if (!isset($file['name'])) throw new \InvalidArgumentException("Invalid file data: {$file->asXML()}");

    $sourceFile = (string) $file['name'];
    $source = (string) @file_get_contents($sourceFile);
    if (!mb_strlen($source)) throw new \RuntimeException("Source file not found: $sourceFile");

    $coverage = new \SplFixedArray(count(preg_split('/\r?\n/', $source) ?: []));
    foreach ($file->line as $line) {
      if (!isset($line['type']) || (string) $line['type'] != 'stmt') continue;
      $lineNumber = max(1, (int) $line['num']);
      $coverage[$lineNumber - 1] = max(0, (int) $line['count']);
    }

    $filename = Path::makeRelative($sourceFile, $workingDir);
    return new SourceFile($filename, md5($source), $source, $coverage->toArray());
  }, $files));
}
