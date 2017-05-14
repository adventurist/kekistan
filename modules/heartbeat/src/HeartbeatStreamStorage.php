<?php

namespace Drupal\heartbeat;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class HeartbeatStreamStorage extends SqlContentEntityStorage implements HeartbeatStreamStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(HeartbeatStreamInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {heartbeat_stream_revision} WHERE id=:id ORDER BY vid',
      array(':id' => $entity->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {heartbeat_stream_field_revision} WHERE uid = :uid ORDER BY vid',
      array(':uid' => $account->id())
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(HeartbeatStreamInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {heartbeat_stream_field_revision} WHERE id = :id AND default_langcode = 1', array(':id' => $entity->id()))
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('heartbeat_stream_revision')
      ->fields(array('langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED))
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
