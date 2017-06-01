<?php

namespace Drupal\heartbeat;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeRepository;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class HeartbeatStreamServices.
 *
 * @package Drupal\heartbeat
 */
class HeartbeatStreamServices {


  protected $lastId;

  protected $latestTimestamp;

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
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor.
   * @param EntityTypeManager $entityTypeManager
   * @param EntityTypeRepository $entityTypeRepository
   * @param QueryFactory $entityQuery
   */
  public function __construct(EntityTypeManager $entityTypeManager, EntityTypeRepository $entityTypeRepository, QueryFactory $entityQuery, ConfigFactoryInterface $configFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeRepository = $entityTypeRepository;
    $this->entityQuery = $entityQuery;
    $this->configFactory = $configFactory;
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


  public function loadStream($type) {
    return $this->entityQuery->get('heartbeat_stream')->condition('name', $type)->execute();
  }


  /*
   * Load all available HeartbeatStream entities
   */
  public function getAllStreams() {
    return $this->entityTypeManager->getStorage('heartbeat_stream')->loadMultiple($this->loadAllEntities());
  }

  public function createStreamForUids($uids) {
    return $this->entityTypeManager->getStorage('heartbeat')->loadMultiple($this->entityQuery->get('heartbeat')->condition('status', 1)->condition('uid', $uids, 'IN')->sort('created', 'DESC')->execute());
  }


  public function createStreamForUidsByType($uids, $type) {
    $stream = $this->entityTypeManager->getStorage('heartbeat_stream')->load(array_values($this->loadStream($type))[0]);
    if ($stream !== null) {
      $beats = $this->entityTypeManager->getStorage('heartbeat')->loadMultiple($this->entityQuery->get('heartbeat')->condition('status', 1)->condition('type', array_column($stream->getTypes(), 'target_id'), 'IN')->condition('uid', $uids, 'IN')->sort('created', 'DESC')->execute());

      $this->lastId = call_user_func('end', array_keys($beats));

      $this->configFactory->getEditable('heartbeat_update_feed.settings')->set('lastId', $this->lastId)->set('update', false)->set('timestamp', array_values($beats)[0]->getRevisionCreationTime())->save();

      return $beats;
    }
  }

  public function updateStreamForUidsByType($uids, $type) {
    $stream = $this->entityTypeManager->getStorage('heartbeat_stream')->load(array_values($this->loadStream($type))[0]);

    return $this->entityTypeManager->getStorage('heartbeat')->loadMultiple($this->entityQuery->get('heartbeat')->condition('status', 1)->condition('revision_created', $this->latestTimestamp, '>')->condition('type', array_column($stream->getTypes(), 'target_id'), 'IN')->condition('uid', $uids, 'IN')->sort('created', 'DESC')->execute());

  }

}
