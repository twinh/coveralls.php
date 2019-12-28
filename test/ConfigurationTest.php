<?php declare(strict_types=1);
namespace Coveralls;

use function PHPUnit\Expect\{expect, it};
use PHPUnit\Framework\{TestCase};

/** @testdox Coveralls\Configuration */
class ConfigurationTest extends TestCase {

  /** @testdox ::fromEnvironment() */
  function testFromEnvironment(): void {
    it('should return an empty configuration for an empty environment', function() {
      expect(Configuration::fromEnvironment([]))->to->be->empty;
    });

    it('should return an initialized instance for a non-empty environment', function() {
      $configuration = Configuration::fromEnvironment([
        'CI_NAME' => 'travis-pro',
        'CI_PULL_REQUEST' => 'PR #123',
        'COVERALLS_REPO_TOKEN' => '0123456789abcdef',
        'GIT_MESSAGE' => 'Hello World!',
        'TRAVIS' => 'true',
        'TRAVIS_BRANCH' => 'develop'
      ]);

      expect($configuration['commit_sha'])->to->be->null;
      expect($configuration['git_message'])->to->equal('Hello World!');
      expect($configuration['repo_token'])->to->equal('0123456789abcdef');
      expect($configuration['service_branch'])->to->equal('develop');
      expect($configuration['service_name'])->to->equal('travis-pro');
      expect($configuration['service_pull_request'])->to->equal('123');
    });
  }

  /** @testdox ::fromYaml() */
  function testFromYaml(): void {
    it('should return an initialized instance for a non-empty map', function() {
      $configuration = Configuration::fromYaml("repo_token: 0123456789abcdef\nservice_name: travis-ci");
      expect($configuration)->to->have->lengthOf(2);
      expect($configuration['repo_token'])->to->equal('0123456789abcdef');
      expect($configuration['service_name'])->to->equal('travis-ci');
    });

    it('should throw an exception with a non-object value', function() {
      expect(fn() => Configuration::fromYaml('foo'))->to->throw(\InvalidArgumentException::class);
    });
  }

  /** @testdox ::loadDefaults() */
  function testLoadDefaults(): void {
    it('should properly initialize from a `.coveralls.yml` file', function() {
      $configuration = Configuration::loadDefaults('test/fixtures/.coveralls.yml');
      expect($configuration)->to->have->length->of->at->least(2);
      expect($configuration['repo_token'])->to->equal('yYPv4mMlfjKgUK0rJPgN0AwNXhfzXpVwt');
      expect($configuration['service_name'])->to->equal('travis-pro');
    });

    it('should use the environment defaults if the `.coveralls.yml` file is not found', function() {
      $configuration = Configuration::loadDefaults('.dummy/config.yml');
      $defaults = Configuration::fromEnvironment();
      expect(get_object_vars($configuration->jsonSerialize()))->to->equal(get_object_vars($defaults->jsonSerialize()));
    });
  }

  /** @testdox ->count() */
  function testCount(): void {
    it('should return zero for an empty configuration', function() {
      expect(new Configuration)->to->be->empty;
    });

    it('should return the number of entries for a non-empty configuration', function() {
      expect(new Configuration(['foo' => 'bar', 'bar' => 'baz']))->to->have->lengthOf(2);
    });
  }

  /** @testdox ->getIterator() */
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

  /** @testdox ->getKeys() */
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

  /** @testdox ->jsonSerialize() */
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

  /** @testdox ->merge() */
  function testMerge(): void {
    it('should have the same entries as the other configuration', function() {
      $configuration = new Configuration;
      expect($configuration)->to->be->empty;

      $configuration->merge(new Configuration(['foo' => 'bar', 'bar' => 'baz']));
      expect($configuration)->to->have->lengthOf(2);
      expect($configuration['foo'])->to->equal('bar');
      expect($configuration['bar'])->to->equal('baz');
    });
  }

  /** @testdox ->offsetExists() */
  function testOffsetExists(): void {
    it('should handle the existence of an element', function() {
      $configuration = new Configuration;
      expect($configuration->offsetExists('foo'))->to->be->false;
      $configuration['foo'] = 'bar';
      expect($configuration->offsetExists('foo'))->to->be->true;
    });
  }

  /** @testdox ->offsetGet() */
  function testOffsetGet(): void {
    it('should handle the fetch of an element', function() {
      $configuration = new Configuration;
      expect($configuration->offsetGet('foo'))->to->be->null;
      $configuration['foo'] = 'bar';
      expect($configuration->offsetGet('foo'))->to->equal('bar');
    });
  }

  /** @testdox ->offsetSet() */
  function testOffsetSet(): void {
    it('should handle the setting of an element', function() {
      $configuration = new Configuration;
      expect($configuration['foo'])->to->be->null;
      $configuration->offsetSet('foo', 'bar');
      expect($configuration['foo'])->to->equal('bar');
    });
  }

  /** @testdox ->offsetUnset() */
  function testOffsetUnset(): void {
    it('should handle the unsetting of an element', function() {
      $configuration = new Configuration(['foo' => 'bar']);
      expect(isset($configuration['foo']))->to->be->true;
      $configuration->offsetUnset('foo');
      expect(isset($configuration['foo']))->to->be->false;
    });
  }
}
