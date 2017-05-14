<?php

namespace Drupal\heartbeat8;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeRepository;

/**
 * Class HeartbeatStreamServices.
 *
 * @package Drupal\heartbeat8
 */
class HeartbeatStreamServices {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entity_type_manager;

  /**
   * Drupal\Core\Entity\EntityTypeRepository definition.
   *
   * @var Drupal\Core\Entity\EntityTypeRepository
   */
  protected $entity_type_repository;
  /**
   * Constructor.
   */
  public function __construct(EntityTypeManager $entity_type_manager, EntityTypeRepository $entity_type_repository) {
    $this->entity_type_manager = $entity_type_manager;
    $this->entity_type_repository = $entity_type_repository;
  }

  /**
   * Returns a loaded HeartbeatStream entity
   * @param $id
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public function getEntityById($id) {
    return $this->entity_type_manager->getStorage('heartbeat_stream')->load($id);
  }


  /**
   * Returns an array of HeartbeatType strings for a given
   * HeartbeatStream specified by ID
   * @param $id
   * @return mixed
   */
  public function getTypesById($id) {
    return $this->entity_type_manager->getStorage('heartbeat_stream')->load($id)->get('types');
  }


}
