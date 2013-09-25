<?php

/**
 * @file
 * Contains \Drupal\Core\Asset\StringAsset.
 */

namespace Drupal\Core\Asset;

use Assetic\Filter\FilterInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Asset\BaseAsset;
use Drupal\Core\Asset\Metadata\AssetMetadataBag;

class StringAsset extends BaseAsset {

  /**
   * The string id of this asset.
   *
   * This is generated by hashing the content of the asset when the object is
   * first created. The id does NOT change if the content is changed later.
   *
   * @var string
   */
  protected $id;

  protected $lastModified;

  public function __construct(AssetMetadataBag $metadata, $content, $filters = array()) {
    if (!is_string($content)) {
      throw new \InvalidArgumentException('StringAsset requires a string for its content.');
    }

    $this->id= empty($content) ? Crypt::randomStringHashed(32) : hash('sha256', $content);
    $this->setContent($content);
    $this->lastModified = REQUEST_TIME; // TODO this is terrible

    parent::__construct($metadata, $filters);
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  public function setLastModified($last_modified) {
    $this->lastModified = $last_modified;
  }

  public function getLastModified() {
    return $this->lastModified;
  }

  public function load(FilterInterface $additionalFilter = NULL) {
    $this->doLoad($this->getContent(), $additionalFilter);
  }
}