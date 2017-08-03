<?php
declare(strict_types=1);
namespace Coveralls;

use EventLoop\{EventLoop};

/**
 * Defines the fixture to run multiple tests.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase {

  /**
   * Waits until there are no more tasks to perform.
   */
  protected function wait() {
    $loop = EventLoop::getLoop();
    $loop->futureTick([$loop, 'stop']);
    $loop->run();
  }
}
