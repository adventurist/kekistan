<?php

namespace Drupal\heartbeat;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;

/**
 * Class HeartbeatService.
 *
 * @package Drupal\heartbeat
 */
class HeartbeatService {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;
  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Constructor.
   * @param EntityTypeManager $entity_type_manager
   * @param QueryFactory $entity_query
   */
  public function __construct(EntityTypeManager $entity_type_manager, QueryFactory $entity_query) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityQuery = $entity_query;
  }

  public function loadAll() {
    $entities = $this->entityQuery->get("heartbeat")->execute();

    return $this->entityTypeManager->getStorage("heartbeat")->loadMultiple($entities);

  }

  public function load($id) {
    return $this->entityTypeManager->getStorage("heartbeat")->load($id);
  }

  public function loadByType($type) {
    return $this->entityTypeManager->getStorage("heartbeat")->loadMultiple($this->entityQuery->get('heartbeat')->condition('type', $type)->execute());
  }

  public function loadByTypes($types) {
    return $this->entityTypeManager->getStorage("heartbeat")->loadMultiple($this->entityQuery->get('heartbeat')->condition('type', $types, 'IN')->sort('created', 'DESC')->execute());
  }

  public function loadByNid($nid) {
    return $this->entityTypeManager->getStorage('heartbeat')->load($this->entityQuery->get('heartbeat')->condition('nid', $nid)->sort('created', 'DESC')->execute());
  }

  public function loadByUid($uid) {
    $hid = $this->entityQuery->get('heartbeat')
      ->condition('uid', $uid)
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0,1)

      ->execute();

    return $this->entityTypeManager->getStorage('heartbeat')->load(array_values($hid)[0]);
  }
}

