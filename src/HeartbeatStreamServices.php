<?php

namespace Drupal\heartbeat;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeRepository;
use Drupal\Core\Entity\Query\QueryFactory;

/**
 * Class HeartbeatStreamServices.
 *
 * @package Drupal\heartbeat
 */
class HeartbeatStreamServices {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Entity\EntityTypeRepository definition.
   *
   * @var EntityTypeRepository
   */
  protected $entityTypeRepository;

  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;


  /**
   * Constructor.
   * @param EntityTypeManager $entityTypeManager
   * @param EntityTypeRepository $entityTypeRepository
   * @param QueryFactory $entityQuery
   */
  public function __construct(EntityTypeManager $entityTypeManager, EntityTypeRepository $entityTypeRepository, QueryFactory $entityQuery) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeRepository = $entityTypeRepository;
    $this->entityQuery = $entityQuery;
  }

  /**
   * Returns a loaded HeartbeatStream entity
   * @param $id
   * @return \Drupal\Core\Entity\EntityInterface|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getEntityById($id) {
    return $this->entityTypeManager->getStorage('heartbeat_stream')->load($id);
  }


  /**
   * Returns an array of HeartbeatType strings for a given
   * HeartbeatStream specified by ID
   * @param $id
   * @return mixed
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getTypesById($id) {
    return $this->entityTypeManager->getStorage('heartbeat_stream')->load($id)->get('types');
  }

  /**
   * Returns an array of HeartbeatStream entities
   * HeartbeatStream specified by ID
   * @return mixed
   */
  public function loadAllEntities() {
    return $this->entityQuery->get('heartbeat_stream')->execute();
  }


  /*
   * Load all available HeartbeatStream entities
   */
  public function getAllStreams() {
    return $this->entityTypeManager->getStorage('heartbeat_stream')->loadMultiple($this->loadAllEntities());
  }

}
