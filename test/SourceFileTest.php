<?php
/**
 * Implementation of the `coveralls\test\SourceFileTest` class.
 */
namespace coveralls\test;

use Codeception\{Specify};
use coveralls\{SourceFile};
use PHPUnit\Framework\{TestCase};

/**
 * @coversDefaultClass \coveralls\SourceFile
 */
class SourceFileTest extends TestCase {
  use Specify;

  /**
   * @test ::fromJSON
   */
  public function testFromJSON() {
    $this->specify('should return a null reference with a non-object value', function() {
      $this->assertNull(SourceFile::fromJSON('foo'));
    });

    $this->specify('should return an instance with default values for an empty map', function() {
      $file = SourceFile::fromJSON([]);
      $this->assertInstanceOf(SourceFile::class, $file);
      $this->assertCount(0, $file->getCoverage());
      $this->assertEmpty($file->getName());
      $this->assertEmpty($file->getSource());
      $this->assertEmpty($file->getSourceDigest());
    });

    $this->specify('should return an initialized instance for a non-empty map', function() {
      $file = SourceFile::fromJSON([
        'coverage' => [null, 2, 0, null, 4, 15, null],
        'name' => 'coveralls.php',
        'source' => 'function main() {}',
        'source_digest' => 'e23fb141da9a7b438479a48eac7b7249'
      ]);

      $this->assertInstanceOf(SourceFile::class, $file);

      $coverage = $file->getCoverage();
      $this->assertCount(7, $coverage);
      $this->assertNull($coverage[0]);
      $this->assertEquals(2, $coverage[1]);

      $this->assertEquals('coveralls.php', $file->getName());
      $this->assertEquals('function main() {}', $file->getSource());
      $this->assertEquals('e23fb141da9a7b438479a48eac7b7249', $file->getSourceDigest());
    });
  }

  /**
   * @test ::jsonSerialize
   */
  public function testJsonSerialize() {
    $this->specify('should return a map with default values for a newly created instance', function() {
      $map = (new SourceFile())->jsonSerialize();
      $this->assertCount(3, get_object_vars($map));
      $this->assertCount(0, $map->coverage);
      $this->assertEmpty($map->name);
      $this->assertEmpty($map->source_digest);
    });

    $this->specify('should return a non-empty map for an initialized instance', function() {
      $map = (new SourceFile(
        'coveralls.php',
        'e23fb141da9a7b438479a48eac7b7249',
        'function main() {}',
        [null, 2, 0, null, 4, 15, null]
      ))->jsonSerialize();

      $this->assertCount(4, get_object_vars($map));
      $this->assertCount(7, $map->coverage);
      $this->assertNull($map->coverage[0]);
      $this->assertEquals(2, $map->coverage[1]);
      $this->assertEquals('coveralls.php', $map->name);
      $this->assertEquals('function main() {}', $map->source);
      $this->assertEquals('e23fb141da9a7b438479a48eac7b7249', $map->source_digest);
    });
  }

  /**
   * @test ::__toString
   */
  public function testToString() {
    $remote = (string) new SourceFile('coveralls.php', 'e23fb141da9a7b438479a48eac7b7249');

    $this->specify('should start with the class name', function() use ($remote) {
      $this->assertStringStartsWith('coveralls\SourceFile {', $remote);
    });

    $this->specify('should contain the instance properties', function() use ($remote) {
      $this->assertContains('"name":"coveralls.php"', $remote);
      $this->assertContains('"source_digest":"e23fb141da9a7b438479a48eac7b7249"', $remote);
    });
  }
}
