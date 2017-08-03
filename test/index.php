<?php
declare(strict_types=1);

use EventLoop\{EventLoop};
use Rx\{Scheduler};

function wait(callable $block) {
  return function() use ($block) {
    $loop = EventLoop::getLoop();

    // call_user_func($block);
    // $loop->futureTick([$loop, 'stop']);
    // $loop->run();

    $loop->futureTick(function() use ($block, $loop) {
      $loop->stop();
      call_user_func($block);
    });

    $loop->run();
  };
}

// Load the class library.
$rootPath = dirname(__DIR__);
require_once "$rootPath/vendor/autoload.php";

// Initialize the application.
ini_set('xdebug.max_nesting_level', '1024');
Scheduler::setDefaultFactory([Scheduler::class, 'getImmediate']);
