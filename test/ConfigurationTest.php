<?php
/**
 * Implementation of the `coveralls\test\ConfigurationTest` class.
 */
namespace coveralls\test;

use coveralls\{Configuration};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \coveralls\Configuration
 */
class ConfigurationTest extends TestCase {

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
    // Should return zero for an empty configuration.
    $this->assertEquals(0, (new Configuration())->count());

    // Should return the number of entries for a non-empty configuration.
    $this->assertEquals(2, (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->count());
  }

  /**
   * @test ::fromEnvironment
   */
  public function testFromEnvironment() {
    // Should return an empty configuration for an empty environment.
    $config = Configuration::fromEnvironment([]);
    $this->assertCount(0, $config);

    // Should return an initialized instance for a non-empty environment.
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
  }

  /**
   * @test ::fromYAML
   */
  public function testFromYAML() {
    // Should return a null reference with a non-object value.
    $this->assertNull(Configuration::fromYAML('**123/456**'));
    $this->assertNull(Configuration::fromYAML('foo'));

    // Should return an initialized instance for a non-empty map.
    $config = Configuration::fromYAML("repo_token: 0123456789abcdef\nservice_name: travis-ci");
    $this->assertInstanceOf(Configuration::class, $config);
    $this->assertCount(2, $config);
    $this->assertEquals('0123456789abcdef', $config['repo_token']);
    $this->assertEquals('travis-ci', $config['service_name']);
  }

  /**
   * @test ::getIterator
   */
  public function testGetIterator() {
    // Should return a done iterator if configuration is empty.
    $iterator = (new Configuration())->getIterator();
    $this->assertFalse($iterator->valid());

    // Should return a value iterator if configuration is not empty.
    $iterator = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->getIterator();
    $this->assertTrue($iterator->valid());

    $this->assertEquals('foo', $iterator->key());
    $this->assertEquals('bar', $iterator->current());
    $iterator->next();

    $this->assertEquals('bar', $iterator->key());
    $this->assertEquals('baz', $iterator->current());
    $iterator->next();

    $this->assertFalse($iterator->valid());
  }

  /**
   * @test ::getKeys
   */
  public function testGetKeys() {
    // Should return an empty array for an empty configuration.
    $this->assertCount(0, (new Configuration())->getKeys());

    // Should return the list of keys for a non-empty configuration.
    $keys = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->getKeys();
    $this->assertCount(2, $keys);
    $this->assertEquals('foo', $keys[0]);
    $this->assertEquals('bar', $keys[1]);
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    // Should return an empty map for a newly created instance.
    $map = (new Configuration())->jsonSerialize();
    $this->assertCount(0, get_object_vars($map));

    // Should return a non-empty map for an initialized instance.
    $map = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->jsonSerialize();
    $this->assertCount(2, get_object_vars($map));
    $this->assertEquals('bar', $map->foo);
    $this->assertEquals('baz', $map->bar);
  }

  /**
   * @test ::loadDefaults
   */
  public function testLoadDefaults() {
    // Should properly initialize from a `.coveralls.yml` file.
    $config = Configuration::loadDefaults(__DIR__.'/fixtures/.coveralls.yml');
    $this->assertTrue(count($config) >= 2);
    $this->assertEquals('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt', $config['repo_token']);
    $this->assertEquals('travis-pro', $config['service_name']);
  }

  /**
   * @test ::merge
   */
  public function testMerge() {
    // Should have the same entries as the other configuration.
    $config = new Configuration();
    $this->assertCount(0, $config);

    $config->merge(new Configuration(['foo' => 'bar', 'bar' => 'baz']));
    $this->assertCount(2, $config);
    $this->assertEquals('bar', $config['foo']);
    $this->assertEquals('baz', $config['bar']);
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $config = (string) new Configuration(['foo' => 'bar', 'bar' => 'baz']);

    // Should start with the class name.
    $this->assertStringStartsWith('coveralls\Configuration {', $config);

    // Should contain the instance properties.
    $this->assertContains('"foo":"bar"', $config);
    $this->assertContains('"bar":"baz"', $config);
  }
}
