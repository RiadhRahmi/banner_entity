<?php

namespace Drupal\banner_entity;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\banner_entity\Entity\BannerInterface;

/**
 * Defines the storage handler class for Banner entities.
 *
 * This extends the base storage class, adding required special handling for
 * Banner entities.
 *
 * @ingroup banner_entity
 */
interface BannerStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Banner revision IDs for a specific Banner.
   *
   * @param \Drupal\banner_entity\Entity\BannerInterface $entity
   *   The Banner entity.
   *
   * @return int[]
   *   Banner revision IDs (in ascending order).
   */
  public function revisionIds(BannerInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Banner author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Banner revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\banner_entity\Entity\BannerInterface $entity
   *   The Banner entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(BannerInterface $entity);

  /**
   * Unsets the language for all Banner with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
