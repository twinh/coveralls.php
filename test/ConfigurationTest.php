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
   * @test ::offsetExists
   * @test ::offsetGet
   * @test ::offsetSet
   * @test ::offsetUnset
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
    $this->assertEquals(0, (new Configuration())->count());
    $this->assertEquals(2, (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->count());
  }

  /**
   * @test ::fromEnvironment
   */
  public function testFromEnvironment() {
    $config = Configuration::fromEnvironment([]);
    $this->assertCount(0, $config);

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
    $this->assertNull(Configuration::fromYAML('**123/456**'));
    $this->assertNull(Configuration::fromYAML('foo'));

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
    $iterator = (new Configuration())->getIterator();
    $this->assertFalse($iterator->valid());

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
    $this->assertCount(0, (new Configuration())->getKeys());

    $keys = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->getKeys();
    $this->assertCount(2, $keys);
    $this->assertEquals('foo', $keys[0]);
    $this->assertEquals('bar', $keys[1]);
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    $map = (new Configuration())->jsonSerialize();
    $this->assertCount(0, get_object_vars($map));

    $map = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->jsonSerialize();
    $this->assertCount(2, get_object_vars($map));
    $this->assertEquals('bar', $map->foo);
    $this->assertEquals('baz', $map->bar);
  }

  /**
   * @test ::loadDefaults
   */
  public function testLoadDefaults() {
    $config = Configuration::loadDefaults(__DIR__.'/fixtures/.coveralls.yml');
    $this->assertTrue(count($config) >= 2);
    $this->assertEquals('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt', $config['repo_token']);
    $this->assertEquals('travis-pro', $config['service_name']);
  }

  /**
   * @test ::merge
   */
  public function testMerge() {
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
    $this->assertStringStartsWith('coveralls\Configuration {', $config);
    $this->assertContains('"foo":"bar"', $config);
    $this->assertContains('"bar":"baz"', $config);
  }
}
