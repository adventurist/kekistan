<?php

namespace Drupal\heartbeat8;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Heartbeat entity.
 *
 * @see \Drupal\heartbeat8\Entity\Heartbeat.
 */
class HeartbeatAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\heartbeat8\Entity\HeartbeatInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished heartbeat entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published heartbeat entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit heartbeat entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete heartbeat entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add heartbeat entities');
  }

}
