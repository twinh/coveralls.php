<?php declare(strict_types=1);
use Coveralls\{Client, ClientException};

/** Uploads a coverage report. */
function main(): void {
	try {
		$coverage = file_get_contents("/path/to/coverage.report");
		(new Client)->upload($coverage);
		print "The report was sent successfully.";
	}

	catch (Throwable $e) {
		print "An error occurred: {$e->getMessage()}" . PHP_EOL;
		if ($e instanceof ClientException) print "From: {$e->getUri()}" . PHP_EOL;
	}
}
