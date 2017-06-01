<?php

namespace Drupal\heartbeat;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\heartbeat\Entity\HeartbeatInterface;

/**
 * Defines the storage handler class for Heartbeat entities.
 *
 * This extends the base storage class, adding required special handling for
 * Heartbeat entities.
 *
 * @ingroup heartbeat
 */
interface HeartbeatStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Heartbeat revision IDs for a specific Heartbeat.
   *
   * @param \Drupal\heartbeat\Entity\HeartbeatInterface $entity
   *   The Heartbeat entity.
   *
   * @return int[]
   *   Heartbeat revision IDs (in ascending order).
   */
  public function revisionIds(HeartbeatInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Heartbeat author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Heartbeat revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\heartbeat\Entity\HeartbeatInterface $entity
   *   The Heartbeat entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(HeartbeatInterface $entity);

  /**
   * Unsets the language for all Heartbeat with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
