<?php

namespace Drupal\heartbeat8;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Heartbeat stream entity.
 *
 * @see \Drupal\heartbeat8\Entity\HeartbeatStream.
 */
class HeartbeatStreamAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\heartbeat8\Entity\HeartbeatStreamInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished heartbeat stream entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published heartbeat stream entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit heartbeat stream entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete heartbeat stream entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add heartbeat stream entities');
  }

}
