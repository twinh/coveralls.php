<?php declare(strict_types=1);
namespace Coveralls;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/** Tests the features of the `Coveralls\Configuration` class. */
class ConfigurationTest extends TestCase {

  /** @test Configuration::fromEnvironment() */
  function testFromEnvironment(): void {
    it('should return an empty configuration for an empty environment', function() {
      expect(Configuration::fromEnvironment([]))->to->be->empty;
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

  /** @test Configuration::fromYaml() */
  function testFromYaml(): void {
    it('should return an initialized instance for a non-empty map', function() {
      $config = Configuration::fromYaml("repo_token: 0123456789abcdef\nservice_name: travis-ci");
      expect($config)->to->have->lengthOf(2);
      expect($config['repo_token'])->to->equal('0123456789abcdef');
      expect($config['service_name'])->to->equal('travis-ci');
    });

    it('should throw an exception with a non-object value', function() {
      expect(function() { Configuration::fromYaml('foo'); })->to->throw(\InvalidArgumentException::class);
    });
  }

  /** @test Configuration::loadDefaults() */
  function testLoadDefaults(): void {
    it('should properly initialize from a `.coveralls.yml` file', function() {
      $config = Configuration::loadDefaults('test/fixtures/.coveralls.yml');
      expect($config)->to->have->length->of->at->least(2);
      expect($config['repo_token'])->to->equal('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt');
      expect($config['service_name'])->to->equal('travis-pro');
    });

    it('should use the environment defaults if the `.coveralls.yml` file is not found', function() {
      $defaults = Configuration::fromEnvironment();
      $config = Configuration::loadDefaults('.dummy/config.yml');
      expect(get_object_vars($config->jsonSerialize()))->to->equal(get_object_vars($defaults->jsonSerialize()));
    });
  }

  /** @test Configuration->count() */
  function testCount(): void {
    it('should return zero for an empty configuration', function() {
      expect(new Configuration)->to->be->empty;
    });

    it('should return the number of entries for a non-empty configuration', function() {
      expect(new Configuration(['foo' => 'bar', 'bar' => 'baz']))->to->have->lengthOf(2);
    });
  }

  /** @test Configuration->getIterator() */
  function testGetIterator(): void {
    it('should return a done iterator if configuration is empty', function() {
      $iterator = (new Configuration)->getIterator();
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

  /** @test Configuration->getKeys() */
  function testGetKeys(): void {
    it('should return an empty array for an empty configuration', function() {
      expect((new Configuration)->getKeys())->to->be->empty;
    });

    it('should return the list of keys for a non-empty configuration', function() {
      $keys = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->getKeys();
      expect($keys)->to->have->lengthOf(2);
      expect($keys[0])->to->equal('foo');
      expect($keys[1])->to->equal('bar');
    });
  }

  /** @test Configuration->jsonSerialize() */
  function testJsonSerialize(): void {
    it('should return an empty map for a newly created instance', function() {
      $map = (new Configuration)->jsonSerialize();
      expect(get_object_vars($map))->to->be->empty;
    });

    it('should return a non-empty map for an initialized instance', function() {
      $map = (new Configuration(['foo' => 'bar', 'bar' => 'baz']))->jsonSerialize();
      expect(get_object_vars($map))->to->have->lengthOf(2);
      expect($map->foo)->to->equal('bar');
      expect($map->bar)->to->equal('baz');
    });
  }

  /** @test Configuration->merge() */
  function testMerge(): void {
    it('should have the same entries as the other configuration', function() {
      $config = new Configuration;
      expect($config)->to->be->empty;

      $config->merge(new Configuration(['foo' => 'bar', 'bar' => 'baz']));
      expect($config)->to->have->lengthOf(2);
      expect($config['foo'])->to->equal('bar');
      expect($config['bar'])->to->equal('baz');
    });
  }

  /** @test Configuration->offsetExists() */
  function testOffsetExists(): void {
    it('should handle the existence of an element', function() {
      $config = new Configuration;
      expect($config->offsetExists('foo'))->to->be->false;
      $config['foo'] = 'bar';
      expect($config->offsetExists('foo'))->to->be->true;
    });
  }

  /** @test Configuration->offsetGet() */
  function testOffsetGet(): void {
    it('should handle the fetch of an element', function() {
      $config = new Configuration;
      expect($config->offsetGet('foo'))->to->be->null;
      $config['foo'] = 'bar';
      expect($config->offsetGet('foo'))->to->equal('bar');
    });
  }

  /** @test Configuration->offsetSet() */
  function testOffsetSet(): void {
    it('should handle the setting of an element', function() {
      $config = new Configuration;
      expect($config['foo'])->to->be->null;
      $config->offsetSet('foo', 'bar');
      expect($config['foo'])->to->equal('bar');
    });
  }

  /** @test Configuration->offsetUnset() */
  function testOffsetUnset(): void {
    it('should handle the unsetting of an element', function() {
      $config = new Configuration(['foo' => 'bar']);
      expect(isset($config['foo']))->to->be->true;
      $config->offsetUnset('foo');
      expect(isset($config['foo']))->to->be->false;
    });
  }
}
