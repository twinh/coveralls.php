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

    it('should handle the existence of an element', function() use ($config) {
      expect($config->offsetExists('foo'))->to->be->false;
      $config->offsetSet('foo', 'bar');
      expect($config->offsetExists('foo'))->to->be->true;
      $config->offsetUnset('foo');
    });

    it('should handle the fetch of an element', function() use ($config) {
      expect($config->offsetGet('foo'))->to->be->null;
      $config->offsetSet('foo', 'bar');
      expect($config->offsetGet('foo'))->to->equal('bar');
      $config->offsetUnset('foo');
    });
  }

  /**
   * @test ::count
   */
  public function testCount() {
    it('should return zero for an empty configuration', function() {
      expect(new Configuration())->to->have->lengthOf(0);
    });

    it('should return the number of entries for a non-empty configuration', function() {
      expect(new Configuration(['foo' => 'bar', 'bar' => 'baz']))->to->have->lengthOf(2);
    });
  }

  /**
   * @test ::fromEnvironment
   */
  public function testFromEnvironment() {
    it('should return an empty configuration for an empty environment', function() {
      $config = Configuration::fromEnvironment([]);
      expect($config)->to->have->lengthOf(0);
    });

    it('should return an initialized instance for a non-empty environment', function() {
      $config = Configuration::fromEnvironment([
        'CI_NAME' => 'travis-pro',
        'CI_PULL_REQUEST' => 'PR #123',
        'COVERALLS_REPO_TOKEN' => '0123456789abcdef',
        'GIT_MESSAGE' => 'Hello World!',
        'TRAVIS' => 'true',
        'TRAVIS_BRANCH' => 'develop'
      ]);

      expect($config['commit_sha'])->to->equal('HEAD');
      expect($config['git_message'])->to->equal('Hello World!');
      expect($config['repo_token'])->to->equal('0123456789abcdef');
      expect($config['service_branch'])->to->equal('develop');
      expect($config['service_name'])->to->equal('travis-pro');
      expect($config['service_pull_request'])->to->equal('123');
    });
  }

  /**
   * @test ::fromYAML
   */
  public function testFromYAML() {
    it('should return a null reference with a non-object value', function() {
      expect(Configuration::fromYAML('**123/456**'))->to->be->null;
      expect(Configuration::fromYAML('foo'))->to->be->null;
    });

    it('should return an initialized instance for a non-empty map', function() {
      $config = Configuration::fromYAML("repo_token: 0123456789abcdef\nservice_name: travis-ci");
      expect($config)->to->be->instanceOf(Configuration::class);
      expect($config)->to->have->lengthOf(2);
      expect($config['repo_token'])->to->equal('0123456789abcdef');
      expect($config['service_name'])->to->equal('travis-ci');
    });
  }

  /**
   * @test ::getIterator
   */
  public function testGetIterator() {
    it('should return a done iterator if configuration is empty', function() {
      $iterator = (new Configuration())->getIterator();
      expect($iterator->valid())->to->be->false;
    });

    it('should return a value iterator if configuration is not empty', function() {
      $iterator = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->getIterator();
      expect($iterator->valid())->to->be->true;

      expect($iterator->key())->to->equal('foo');
      expect($iterator->current())->to->equal('bar');
      $iterator->next();

      expect($iterator->key())->to->equal('bar');
      expect($iterator->current())->to->equal('baz');
      $iterator->next();

      expect($iterator->valid())->to->be->false;
    });
  }

  /**
   * @test ::getKeys
   */
  public function testGetKeys() {
    it('should return an empty array for an empty configuration', function() {
      expect((new Configuration())->getKeys())->to->be->empty;
    });

    it('should return the list of keys for a non-empty configuration', function() {
      $keys = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->getKeys();
      expect($keys)->to->have->lengthOf(2);
      expect($keys[0])->to->equal('foo');
      expect($keys[1])->to->equal('bar');
    });
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    it('should return an empty map for a newly created instance', function() {
      $map = (new Configuration())->jsonSerialize();
      expect(get_object_vars($map))->to->be->empty;
    });

    it('should return a non-empty map for an initialized instance', function() {
      $map = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(2);
      expect($map->foo)->to->equal('bar');
      expect($map->bar)->to->equal('baz');
    });
  }

  /**
   * @test ::loadDefaults
   */
  public function testLoadDefaults() {
    it('should properly initialize from a `.coveralls.yml` file', function() {
      $config = Configuration::loadDefaults(__DIR__.'/fixtures/.coveralls.yml');
      expect($config)->to->have->length->of->at->least(2);
      expect($config['repo_token'])->to->equal('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt');
      expect($config['service_name'])->to->equal('travis-pro');
    });
  }

  /**
   * @test ::merge
   */
  public function testMerge() {
    it('should have the same entries as the other configuration', function() {
      $config = new Configuration();
      expect($config)->to->have->lengthOf(0);

      $config->merge(new Configuration(['foo' => 'bar', 'bar' => 'baz']));
      expect($config)->to->have->lengthOf(2);
      expect($config['foo'])->to->equal('bar');
      expect($config['bar'])->to->equal('baz');
    });
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $config = (string) new Configuration(['foo' => 'bar', 'bar' => 'baz']);

    it('should start with the class name', function() use ($config) {
      expect($config)->startWith('coveralls\Configuration {');
    });

    it('should contain the instance properties', function() use ($config) {
      expect($config)->to->contain('"bar":"baz"')->and->contain('"foo":"bar"');
    });
  }
}
