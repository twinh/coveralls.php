<?php
declare(strict_types=1);
namespace Coveralls;

use GuzzleHttp\Psr7\{Uri};
use Psr\Http\Message\{UriInterface};

/**
 * Represents a Git remote repository.
 */
class GitRemote implements \JsonSerializable {

  /**
   * @var string The remote's name.
   */
  private $name;

  /**
   * @var UriInterface|null The remote's URL.
   */
  private $url;

  /**
   * Creates a new Git remote repository.
   * @param string $name The remote's name.
   * @param UriInterface $url The remote's URL.
   */
  function __construct(string $name, UriInterface $url = null) {
    $this->name = $name;
    $this->url = $url;
  }

  /**
   * Returns a string representation of this object.
   * @return string The string representation of this object.
   */
  function __toString(): string {
    $json = json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return static::class." $json";
  }

  /**
   * Creates a new remote repository from the specified JSON map.
   * @param object $map A JSON map representing a remote repository.
   * @return static The instance corresponding to the specified JSON map.
   */
  static function fromJson(object $map): self {
    return new static(
      isset($map->name) && is_string($map->name) ? $map->name : '',
      isset($map->url) && is_string($map->url) ? new Uri($map->url) : null
    );
  }

  /**
   * Gets the name of this remote.
   * @return string The remote's name.
   */
  function getName(): string {
    return $this->name;
  }

  /**
   * Gets the URL of this remote.
   * @return UriInterface The remote's URL.
   */
  function getUrl(): ?UriInterface {
    return $this->url;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  function jsonSerialize(): \stdClass {
    return (object) [
      'name' => $this->getName(),
      'url' => ($url = $this->getUrl()) ? (string) $url : null
    ];
  }
}
