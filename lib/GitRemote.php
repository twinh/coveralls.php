<?php
/**
 * Implementation of the `coveralls\GitRemote` class.
 */
namespace coveralls;

/**
 * Represents a Git remote repository.
 */
class GitRemote implements \JsonSerializable {

  /**
   * @var string The remote's name.
   */
  private $name;

  /**
   * @var string The remote's URL.
   */
  private $url;

  /**
   * Initializes a new instance of the class.
   * @param string $name The remote's name.
   * @param string $url The remote's URL.
   */
  public function __construct(string $name = '', string $url = '') {
    $this->setName($name);
    $this->setURL($url);
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
   * Creates a new remote repository from the specified JSON map.
   * @param mixed $map A JSON map representing a remote repository.
   * @return GitRemote The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
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
   * @return GitRemote This instance.
   */
  public function setName(string $value): self {
    $this->name = $value;
    return $this;
  }

  /**
   * Sets the URL of this remote.
   * @param string $value The new URL.
   * @return GitRemote This instance.
   */
  public function setURL(string $value): self {
    $this->url = $value;
    return $this;
  }
}
