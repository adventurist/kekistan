<?php

namespace Drupal\heartbeat8;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\heartbeat8\Entity\HeartbeatInterface;

/**
 * Defines the storage handler class for Heartbeat entities.
 *
 * This extends the base storage class, adding required special handling for
 * Heartbeat entities.
 *
 * @ingroup heartbeat8
 */
class HeartbeatStorage extends SqlContentEntityStorage implements HeartbeatStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(HeartbeatInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {heartbeat_revision} WHERE id=:id ORDER BY vid',
      array(':id' => $entity->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {heartbeat_field_revision} WHERE uid = :uid ORDER BY vid',
      array(':uid' => $account->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(HeartbeatInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {heartbeat_field_revision} WHERE id = :id AND default_langcode = 1', array(':id' => $entity->id()))
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('heartbeat_revision')
      ->fields(array('langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED))
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
