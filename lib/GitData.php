<?php
/**
 * Implementation of the `coveralls\GitData` class.
 */
namespace coveralls;

/**
 * Represents Git data that can be used to display more information to users.
 */
class GitData {

  /**
   * @var \ArrayObject TODO
   */
  private $remotes;

  /**
   * Initializes a new instance of the class.
   * @param array $remotes TODO
   */
  public function __construct(array $remotes = []) {
    $this->remotes = new \ArrayObject($remotes);
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return static::class." $json";
  }
}
