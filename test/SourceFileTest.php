<?php
/**
 * Implementation of the `coveralls\test\SourceFileTest` class.
 */
namespace coveralls\test;
use coveralls\{SourceFile};

/**
 * Tests the features of the `coveralls\SourceFile` class.
 */
class SourceFileTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the `SourceFile::fromJSON()` method.
   */
  public function testFromJSON() {
    $this->assertNull(SourceFile::fromJSON('foo'));

    $file = SourceFile::fromJSON([]);
    $this->assertInstanceOf(SourceFile::class, $file);
    $this->assertCount(0, $file->getCoverage());
    $this->assertEmpty($file->getName());
    $this->assertEmpty($file->getSource());
    $this->assertEmpty($file->getSourceDigest());

    $file = SourceFile::fromJSON(['coverage' => [null, 2, 0, null, 4, 15, null], 'name' => 'coveralls.php', 'source' => 'function main() {}', 'source_digest' => 'e23fb141da9a7b438479a48eac7b7249']);
    $this->assertInstanceOf(SourceFile::class, $file);

    $coverage = $file->getCoverage();
    $this->assertCount(7, $coverage);
    $this->assertNull($coverage[0]);
    $this->assertEquals(2, $coverage[1]);

    $this->assertEquals('coveralls.php', $file->getName());
    $this->assertEquals('function main() {}', $file->getSource());
    $this->assertEquals('e23fb141da9a7b438479a48eac7b7249', $file->getSourceDigest());
  }

  /**
   * Tests the `SourceFile::jsonSerialize()` method.
   */
  public function testJsonSerialize() {
    $map = (new SourceFile())->jsonSerialize();
    $this->assertCount(3, get_object_vars($map));
    $this->assertCount(0, $map->coverage);
    $this->assertEmpty($map->name);
    $this->assertEmpty($map->source_digest);

    $map = (new SourceFile('coveralls.php', 'e23fb141da9a7b438479a48eac7b7249', 'function main() {}', [null, 2, 0, null, 4, 15, null]))->jsonSerialize();
    $this->assertCount(4, get_object_vars($map));
    $this->assertCount(7, $map->coverage);
    $this->assertNull($map->coverage[0]);
    $this->assertEquals(2, $map->coverage[1]);
    $this->assertEquals('coveralls.php', $map->name);
    $this->assertEquals('function main() {}', $map->source);
    $this->assertEquals('e23fb141da9a7b438479a48eac7b7249', $map->source_digest);
  }
}
