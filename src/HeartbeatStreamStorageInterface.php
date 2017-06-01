<?php

namespace Drupal\heartbeat;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\heartbeat\Entity\HeartbeatStreamInterface;

/**
 * Defines the storage handler class for Heartbeat stream entities.
 *
 * This extends the base storage class, adding required special handling for
 * Heartbeat stream entities.
 *
 * @ingroup heartbeat
 */
interface HeartbeatStreamStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Heartbeat stream revision IDs for a specific Heartbeat stream.
   *
   * @param \Drupal\heartbeat\Entity\HeartbeatStreamInterface $entity
   *   The Heartbeat stream entity.
   *
   * @return int[]
   *   Heartbeat stream revision IDs (in ascending order).
   */
  public function revisionIds(HeartbeatStreamInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Heartbeat stream author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Heartbeat stream revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\heartbeat\Entity\HeartbeatStreamInterface $entity
   *   The Heartbeat stream entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(HeartbeatStreamInterface $entity);

  /**
   * Unsets the language for all Heartbeat stream with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
