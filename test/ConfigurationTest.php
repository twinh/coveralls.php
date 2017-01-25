<?php
/**
 * Implementation of the `coveralls\test\ConfigurationTest` class.
 */
namespace coveralls\test;
use coveralls\{Configuration};

/**
 * Tests the features of the `coveralls\Configuration` class.
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the implementation of the `ArrayAccess` interface.
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
   * Tests the `Configuration::count()` method.
   */
  public function testCount() {
    $this->assertEquals(0, (new Configuration())->count());
    $this->assertEquals(2, (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->count());
  }

  /**
   * Tests the `Configuration::fromEnvironment()` method.
   */
  public function testFromEnvironment() {
    // TODO
  }

  /**
   * Tests the `Configuration::fromYAML()` method.
   */
  public function testFromYAML() {
    // TODO
  }

  /**
   * Tests the `Configuration::getIterator()` method.
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
   * Tests the `SourceFile::jsonSerialize()` method.
   */
  public function testJsonSerialize() {
    $this->assertCount(0, get_object_vars((new Configuration())->jsonSerialize()));

    $map = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->jsonSerialize();
    $this->assertCount(2, get_object_vars($map));
    $this->assertEquals('bar', $map->foo);
    $this->assertEquals('baz', $map->bar);
  }

  /**
   * Tests the `Configuration::loadDefaults()` method.
   */
  public function testLoadDefaults() {
    // TODO
  }

  /**
   * Tests the `Configuration::merge()` method.
   */
  public function testMerge() {
    $config = new Configuration();
    $this->assertCount(0, $config);

    $config->merge(new Configuration(['foo' => 'bar', 'bar' => 'baz']));
    $this->assertCount(2, $config);
    $this->assertEquals('bar', $config['foo']);
    $this->assertEquals('baz', $config['bar']);
  }
}
