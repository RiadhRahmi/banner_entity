<?php

namespace Drupal\banner_entity\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Banner entities.
 *
 * @ingroup banner_entity
 */
interface BannerInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Banner name.
   *
   * @return string
   *   Name of the Banner.
   */
  public function getName();

  /**
   * Sets the Banner name.
   *
   * @param string $name
   *   The Banner name.
   *
   * @return \Drupal\banner_entity\Entity\BannerInterface
   *   The called Banner entity.
   */
  public function setName($name);

  /**
   * Gets the Banner creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Banner.
   */
  public function getCreatedTime();

  /**
   * Sets the Banner creation timestamp.
   *
   * @param int $timestamp
   *   The Banner creation timestamp.
   *
   * @return \Drupal\banner_entity\Entity\BannerInterface
   *   The called Banner entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Banner published status indicator.
   *
   * Unpublished Banner are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Banner is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Banner.
   *
   * @param bool $published
   *   TRUE to set this Banner to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\banner_entity\Entity\BannerInterface
   *   The called Banner entity.
   */
  public function setPublished($published);

  /**
   * Gets the Banner revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Banner revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\banner_entity\Entity\BannerInterface
   *   The called Banner entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Banner revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Banner revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\banner_entity\Entity\BannerInterface
   *   The called Banner entity.
   */
  public function setRevisionUserId($uid);


  /*
   *
   * add actions here
   *
   *
   * */

}
