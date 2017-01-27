<?php
/**
 * Implementation of the `coveralls\GitCommit` class.
 */
namespace coveralls;

/**
 * Represents a Git commit.
 */
class GitCommit {

  /**
   * @var string TODO
   */
  private $id = '';

  /**
   * @var string TODO
   */
  private $authorEmail = '';

  /**
   * @var string TODO
   */
  private $authorName = '';

  /**
   * @var string TODO
   */
  private $committerEmail = '';

  /**
   * @var string TODO
   */
  private $committerName = '';

  /**
   * @var string TODO
   */
  private $message = '';

  /**
   * Initializes a new instance of the class.
   */
  public function __construct(string $id = '', string $message = '') {
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  public function __toString(): string {
    $json = json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return static::class." $json";
  }

  /**
   * Creates a new remote from the specified JSON map.
   * @param mixed $map A JSON map representing a branch data.
   * @return GitCommit The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJSON($map) {
    if (is_array($map)) $map = (object) $map;
    return !is_object($map) ? null : new static(
      isset($map->name) && is_string($map->name) ? $map->name : '',
      isset($map->url) && is_string($map->url) ? $map->url : ''
    );
  }

  /**
   * Gets the name of this remote.
   * @return string The remote's name.
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Gets the URL of this remote.
   * @return string The remote's URL.
   */
  public function getURL(): string {
    return $this->url;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    return (object) [
      'name' => $this->getName(),
      'url' => $this->getURL()
    ];
  }

  /**
   * Sets the name of this remote.
   * @param string $value The new name.
   * @return GitCommit This instance.
   */
  public function setName(string $value): self {
    $this->name = $value;
    return $this;
  }

  /**
   * Sets the URL of this remote.
   * @param string $value The new URL.
   * @return GitCommit This instance.
   */
  public function setURL(string $value): self {
    $this->url = $value;
    return $this;
  }
}
