<?php
/**
 * Implementation of the `coveralls\test\ConfigurationTest` class.
 */
namespace coveralls\test;

use Codeception\{Specify};
use coveralls\{Configuration};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \coveralls\Configuration
 */
class ConfigurationTest extends TestCase {
  use Specify;

  /**
   * @test \ArrayAccess
   */
  public function testArrayAccess() {
    $config = new Configuration();
    $this->assertFalse($config->offsetExists('foo'));
    $this->assertNull($config->offsetGet('foo'));

    $config->offsetSet('foo', 'bar');
    $this->assertTrue($config->offsetExists('foo'));
    $this->assertEquals('bar', $config->offsetGet('foo'));

    $config->offsetUnset('foo');
    $this->assertFalse($config->offsetExists('foo'));
    $this->assertNull($config->offsetGet('foo'));
  }

  /**
   * @test ::count
   */
  public function testCount() {
    $this->specify('should return zero for an empty configuration', function() {
      $this->assertEquals(0, (new Configuration())->count());
    });

    $this->specify('should return the number of entries for a non-empty configuration', function() {
      $this->assertEquals(2, (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->count());
    });
  }

  /**
   * @test ::fromEnvironment
   */
  public function testFromEnvironment() {
    $this->specify('should return an empty configuration for an empty environment', function() {
      $config = Configuration::fromEnvironment([]);
      $this->assertCount(0, $config);
    });

    $this->specify('should return an initialized instance for a non-empty environment', function() {
      $config = Configuration::fromEnvironment([
        'CI_NAME' => 'travis-pro',
        'CI_PULL_REQUEST' => 'PR #123',
        'COVERALLS_REPO_TOKEN' => '0123456789abcdef',
        'GIT_MESSAGE' => 'Hello World!',
        'TRAVIS' => 'true',
        'TRAVIS_BRANCH' => 'develop'
      ]);

      $this->assertEquals('HEAD', $config['commit_sha']);
      $this->assertEquals('Hello World!', $config['git_message']);
      $this->assertEquals('0123456789abcdef', $config['repo_token']);
      $this->assertEquals('develop', $config['service_branch']);
      $this->assertEquals('travis-pro', $config['service_name']);
      $this->assertEquals('123', $config['service_pull_request']);
    });
  }

  /**
   * @test ::fromYAML
   */
  public function testFromYAML() {
    $this->specify('should return a null reference with a non-object value', function() {
      $this->assertNull(Configuration::fromYAML('**123/456**'));
      $this->assertNull(Configuration::fromYAML('foo'));
    });

    $this->specify('should return an initialized instance for a non-empty map', function() {
      $config = Configuration::fromYAML("repo_token: 0123456789abcdef\nservice_name: travis-ci");
      $this->assertInstanceOf(Configuration::class, $config);
      $this->assertCount(2, $config);
      $this->assertEquals('0123456789abcdef', $config['repo_token']);
      $this->assertEquals('travis-ci', $config['service_name']);
    });
  }

  /**
   * @test ::getIterator
   */
  public function testGetIterator() {
    $this->specify('should return a done iterator if configuration is empty', function() {
      $iterator = (new Configuration())->getIterator();
      $this->assertFalse($iterator->valid());
    });

    $this->specify('should return a value iterator if configuration is not empty', function() {
      $iterator = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->getIterator();
      $this->assertTrue($iterator->valid());

      $this->assertEquals('foo', $iterator->key());
      $this->assertEquals('bar', $iterator->current());
      $iterator->next();

      $this->assertEquals('bar', $iterator->key());
      $this->assertEquals('baz', $iterator->current());
      $iterator->next();

      $this->assertFalse($iterator->valid());
    });
  }

  /**
   * @test ::getKeys
   */
  public function testGetKeys() {
    $this->specify('should return an empty array for an empty configuration', function() {
      $this->assertCount(0, (new Configuration())->getKeys());
    });

    $this->specify('should return the list of keys for a non-empty configuration', function() {
      $keys = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->getKeys();
      $this->assertCount(2, $keys);
      $this->assertEquals('foo', $keys[0]);
      $this->assertEquals('bar', $keys[1]);
    });
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    $this->specify('should return an empty map for a newly created instance', function() {
      $map = (new Configuration())->jsonSerialize();
      $this->assertCount(0, get_object_vars($map));
    });

    $this->specify('should return a non-empty map for an initialized instance', function() {
      $map = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->jsonSerialize();
      $this->assertCount(2, get_object_vars($map));
      $this->assertEquals('bar', $map->foo);
      $this->assertEquals('baz', $map->bar);
    });
  }

  /**
   * @test ::loadDefaults
   */
  public function testLoadDefaults() {
    $this->specify('should properly initialize from a `.coveralls.yml` file', function() {
      $config = Configuration::loadDefaults(__DIR__.'/fixtures/.coveralls.yml');
      $this->assertTrue(count($config) >= 2);
      $this->assertEquals('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt', $config['repo_token']);
      $this->assertEquals('travis-pro', $config['service_name']);
    });
  }

  /**
   * @test ::merge
   */
  public function testMerge() {
    $this->specify('should have the same entries as the other configuration', function() {
      $config = new Configuration();
      $this->assertCount(0, $config);

      $config->merge(new Configuration(['foo' => 'bar', 'bar' => 'baz']));
      $this->assertCount(2, $config);
      $this->assertEquals('bar', $config['foo']);
      $this->assertEquals('baz', $config['bar']);
    });
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $config = (string) new Configuration(['foo' => 'bar', 'bar' => 'baz']);

    $this->specify('should start with the class name', function() use ($config) {
      $this->assertStringStartsWith('coveralls\Configuration {', $config);
    });

    $this->specify('should contain the instance properties', function() use ($config) {
      $this->assertContains('"foo":"bar"', $config);
      $this->assertContains('"bar":"baz"', $config);
    });
  }
}
