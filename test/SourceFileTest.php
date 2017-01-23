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
    $data = (new SourceFile())->jsonSerialize();
    $this->assertCount(3, get_object_vars($data));
    $this->assertCount(0, $data->coverage);
    $this->assertEmpty($data->name);
    $this->assertEmpty($data->source_digest);

    $data = (new SourceFile('coveralls.php', 'e23fb141da9a7b438479a48eac7b7249', [null, 2, 0, null, 4, 15, null], 'function main() {}'))->jsonSerialize();
    $this->assertCount(4, get_object_vars($data));
    $this->assertCount(7, $data->coverage);
    $this->assertNull($data->coverage[0]);
    $this->assertEquals(2, $data->coverage[1]);
    $this->assertEquals('coveralls.php', $data->name);
    $this->assertEquals('function main() {}', $data->source);
    $this->assertEquals('e23fb141da9a7b438479a48eac7b7249', $data->source_digest);
  }
}
