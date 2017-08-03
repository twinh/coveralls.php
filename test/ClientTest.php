<?php
declare(strict_types=1);
namespace Coveralls;

use function PHPUnit\Expect\{expect, fail, it};
use PHPUnit\Framework\{TestCase};
use Psr\Http\Message\{UriInterface};
use Rx\Subject\{Subject};

/**
 * Tests the features of the `Coveralls\Client` class.
 */
class ClientTest extends TestCase {

  /**
   * @test Client::onRequest
   */
  public function testOnRequest() {
    it('should return an `Observable` instead of the underlying `Subject`', function() {
      expect((new Client)->onRequest())->to->not->be->instanceOf(Subject::class);
    });
  }

  /**
   * @test Client::onResponse
   */
  public function testOnResponse() {
    it('should return an `Observable` instead of the underlying `Subject`', function() {
      expect((new Client)->onResponse())->to->not->be->instanceOf(Subject::class);
    });
  }

  /**
   * @test Client::parseCloverReport
   */
  public function testParseCloverReport() {
    $parseCloverReport = function($report) {
      return $this->parseCloverReport($report);
    };

    it('should properly parse Clover reports', wait(function() use ($parseCloverReport) {
      $parseCloverReport->call(new Client, file_get_contents('test/fixtures/clover.xml'))->subscribe(function(Job $job) {
        $files = $job->getSourceFiles();
        expect($files)->to->have->lengthOf(3);

        expect($files[0])->to->be->instanceOf(SourceFile::class);
        expect($files[0]->getName())->to->equal('lib/Client.php');
        expect($files[0]->getSourceDigest())->to->not->be->empty;

        $subset = [null, 2, 2, 2, 2, null];
        expect(array_intersect($subset, $files[0]->getCoverage()->getArrayCopy()))->to->equal($subset);

        expect($files[1]->getName())->to->equal('lib/Configuration.php');
        expect($files[1]->getSourceDigest())->to->not->be->empty;

        $subset = [null, 4, 4, 2, 2, 4, 2, 2, 4, 4, null];
        expect(array_intersect($subset, $files[1]->getCoverage()->getArrayCopy()))->to->equal($subset);

        expect($files[2]->getName())->to->equal('lib/GitCommit.php');
        expect($files[2]->getSourceDigest())->to->not->be->empty;

        $subset = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
        expect(array_intersect($subset, $files[2]->getCoverage()->getArrayCopy()))->to->equal($subset);
      });
    }));

    it('should throw an exception if the Clover report is invalid or empty', wait(function() use ($parseCloverReport) {
      $parseCloverReport->call(new Client, '<project></project>')->subscribe(null, function($error) {
        expect($error)->to->be->instanceOf(\InvalidArgumentException::class);
      });
    }));
  }

  /**
   * @test Client::parseLcovReport
   */
  public function testParseLcovReport() {
    it('should properly parse LCOV reports', wait(function() {
      $parseLcovReport = function($report) {
        return $this->parseLcovReport($report);
      };

      $parseLcovReport->call(new Client, file_get_contents('test/fixtures/lcov.info'))->subscribe(function(Job $job) {
        $files = $job->getSourceFiles();
        expect($files)->to->have->lengthOf(3);

        expect($files[0])->to->be->instanceOf(SourceFile::class);
        expect($files[0]->getName())->to->equal('lib/Client.php');
        expect($files[0]->getSourceDigest())->to->not->be->empty;

        $subset = [null, 2, 2, 2, 2, null];
        expect(array_intersect($subset, $files[0]->getCoverage()->getArrayCopy()))->to->equal($subset);

        expect($files[1]->getName())->to->equal('lib/Configuration.php');
        expect($files[1]->getSourceDigest())->to->not->be->empty;

        $subset = [null, 4, 4, 2, 2, 4, 2, 2, 4, 4, null];
        expect(array_intersect($subset, $files[1]->getCoverage()->getArrayCopy()))->to->equal($subset);

        expect($files[2]->getName())->to->equal('lib/GitCommit.php');
        expect($files[2]->getSourceDigest())->to->not->be->empty;

        $subset = [null, 2, 2, 2, 2, 2, 0, 0, 2, 2, null];
        expect(array_intersect($subset, $files[2]->getCoverage()->getArrayCopy()))->to->equal($subset);
      });
    }));
  }

  /**
   * @test Client::setEndPoint
   */
  public function testSetEndPoint() {
    it('should return an instance of `UriInterface` for strings', function() {
      $endPoint = (new Client)->setEndPoint('https://github.com/cedx/free-mobile.php')->getEndPoint();
      expect($endPoint)->to->be->instanceOf(UriInterface::class);
      expect((string) $endPoint)->to->equal('https://github.com/cedx/free-mobile.php');
    });

    it('should return a `null` reference for unsupported values', function() {
      expect((new Client)->setEndPoint(123)->getEndPoint())->to->be->null;
    });
  }

  /**
   * @test Client::updateJob
   */
  public function testUpdateJob() {
    $updateJob = function($job, $config) {
      $this->updateJob($job, $config);
    };

    it('should not modify the job if the configuration is empty', function() use ($updateJob) {
      $job = new Job;
      $updateJob->call(new Client, $job, new Configuration);
      expect($job->getGit())->to->be->null;
      expect($job->isParallel())->to->be->false;
      expect($job->getRepoToken())->to->be->empty;
      expect($job->getRunAt())->to->be->null;
    });

    it('should modify the job if the configuration is not empty', function() use ($updateJob) {
      $job = new Job;
      $updateJob->call(new Client, $job, new Configuration([
        'parallel' => 'true',
        'repo_token' => 'yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt',
        'run_at' => '2017-01-29T03:43:30+01:00',
        'service_branch' => 'develop'
      ]));

      expect($job->isParallel())->to->be->true;
      expect($job->getRepoToken())->to->equal('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt');

      $git = $job->getGit();
      expect($git)->to->be->instanceOf(GitData::class);
      expect($git->getBranch())->to->equal('develop');

      $runAt = $job->getRunAt();
      expect($runAt)->to->be->instanceOf(\DateTime::class);
      expect($runAt->format('c'))->to->equal('2017-01-29T03:43:30+01:00');
    });
  }

  /**
   * @test Client::upload
   */
  public function testUpload() {
    it('should throw an exception with an empty coverage report', wait(function() {
      (new Client)->upload('')->subscribe(null,
        function($error) { expect($error)->to->be->instanceOf(\InvalidArgumentException::class); },
        function() { fail('Exception not thrown.'); }
      );
    }));

    it('should throw an error with an invalid coverage report', wait(function() {
      (new Client)->upload('end_of_record')->subscribe(null,
        function($error) { expect($error)->to->be->instanceOf(\InvalidArgumentException::class); },
        function() { fail('Exception not thrown.'); }
      );
    }));
  }

  /**
   * @test Client::uploadJob
   */
  public function testUploadJob() {
    it('should throw an exception with an empty coverage job', wait(function() {
      (new Client)->uploadJob(new Job)->subscribe(null,
        function($error) { expect($error)->to->be->instanceOf(\InvalidArgumentException::class); },
        function() { fail('Exception not thrown.'); }
      );
    }));
  }
}
