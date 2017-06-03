<?php

namespace Drupal\heartbeat;

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Entity\Query\QueryFactory;

/**
 * Class HeartbeatTypeServices.
 *
 * @package Drupal\heartbeat
 */
class HeartbeatTypeServices {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var EntityTypeManager
   */
  protected $entityTypeManager;

  /**
  +   * Drupal\Core\Entity\EntityTypeBundleInfo definition.
  +   *
  +   * @var EntityTypeBundleInfo
  +   */
  protected $entityTypeBundleInfo;

  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Constructor.
   * @param EntityTypeManager $entityTypeManager
   * @param EntityTypeBundleInfo $entityTypeBundleInfo
   * @param QueryFactory $entity_query
   */
  public function __construct(EntityTypeManager $entityTypeManager, EntityTypeBundleInfo $entityTypeBundleInfo, QueryFactory $entity_query) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    $this->entityQuery = $entity_query;
  }


  public function getTypes() {
    return $this->entityQuery->get('heartbeat_type')->sort('weight', 'ASC')->execute();
  }

  public function load($id) {
    return $this->entityTypeManager->getStorage('heartbeat_type')->load($id);
  }

  public function getEntityBundles(ContentEntityType $entity) {
    return $this->entityTypeBundleInfo->getBundleInfo($entity->id());
  }

}
