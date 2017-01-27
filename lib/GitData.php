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
   * @var string The branch name.
   */
  private $branch;

  /**
   * @var GitCommit The Git commit.
   */
  private $commit;

  /**
   * @var \ArrayObject The remote repositories.
   */
  private $remotes;

  /**
   * Initializes a new instance of the class.
   * @param GitCommit $commit The Git commit.
   * @param string $branch The branch name.
   * @param array $remotes The remote repositories.
   */
  public function __construct(GitCommit $commit = null, string $branch = '', array $remotes = []) {
    $this->setBranch($branch);
    $this->setCommit($commit);
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

  /**
   * Creates a new Git data from the specified JSON map.
   * @param mixed $map A JSON map representing a Git data.
   * @return GitData The instance corresponding to the specified JSON map, or `null` if a parsing error occurred.
   */
  public static function fromJSON($map) {
    $transform = function(array $remotes) {
      return array_filter(array_map(function($item) { return GitRemote::fromJSON($item); }, $remotes));
    };

    if (is_array($map)) $map = (object) $map;
    return !is_object($map) ? null : new static(
      isset($map->head) ? GitCommit::fromJSON($map->head) : null,
      isset($map->branch) && is_string($map->branch) ? $map->branch : '',
      isset($map->remotes) && is_array($map->remotes) ? $transform($map->remotes) : []
    );
  }

  /**
   * Gets the branch name.
   * @return string The branch name.
   */
  public function getBranch(): string {
    return $this->branch;
  }

  /**
   * Gets the Git commit.
   * @return GitCommit The Git commit.
   */
  public function getCommit() {
    return $this->commit;
  }

  /**
   * Gets the remote repositories.
   * @return \ArrayObject The remote repositories.
   */
  public function getRemotes(): \ArrayObject {
    return $this->remotes;
  }

  /**
   * Converts this object to a map in JSON format.
   * @return \stdClass The map in JSON format corresponding to this object.
   */
  public function jsonSerialize(): \stdClass {
    return (object) [
      'branch' => $this->getBranch(),
      'head' => ($commit = $this->getCommit()) ? $commit->jsonSerialize() : null,
      'remotes' => array_map(function(GitRemote $item) { return $item->jsonSerialize(); }, $this->getRemotes()->getArrayCopy())
    ];
  }

  /**
   * Sets the branch name.
   * @param string $value The new name.
   * @return GitData This instance.
   */
  public function setBranch(string $value): self {
    $this->branch = $value;
    return $this;
  }

  /**
   * Sets the Git commit.
   * @param GitCommit $value The new commit.
   * @return GitData This instance.
   */
  public function setCommit(GitCommit $value = null): self {
    $this->commit = $value;
    return $this;
  }

  /**
   * Sets the remote repositories.
   * @param GitRemote[] $value The new remote repositories.
   * @return GitData This instance.
   */
  public function setRemotes(array $value): self {
    $this->getRemotes()->exchangeArray($value);
    return $this;
  }
}
