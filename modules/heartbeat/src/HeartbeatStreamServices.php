<?php

namespace Drupal\heartbeat;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeRepository;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Database\Connection;
//use Drupal\Core\Database\Driver\pgsql\Connection;
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
   * @var \Drupal\Core\Database\Database
   */

  protected $database;

  /**
   * Constructor.
   * @param EntityTypeManager $entityTypeManager
   * @param EntityTypeRepository $entityTypeRepository
   * @param QueryFactory $entityQuery
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param Connection|\Drupal\Core\Database\Database $database
   */
  public function __construct(EntityTypeManager $entityTypeManager, EntityTypeRepository $entityTypeRepository, QueryFactory $entityQuery, ConfigFactoryInterface $configFactory, Connection $database) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeRepository = $entityTypeRepository;
    $this->entityQuery = $entityQuery;
    $this->configFactory = $configFactory;
    $this->database = $database;
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

  public function loadAllStreams() {
    $types = null;

    foreach ($this->getAllStreams() as $stream) {
      foreach ($stream->getTypes() as $type) {
        $type = $type->getValue();
        $type = key($type) === 'target_id' ? $type : $type->getValue()[0];
        if (strlen($type['target_id']) > 1) {
          $types[] = $type;
        }
      }
    }
    $cleanTypes = array_column($types, 'target_id');
    return $this->entityTypeManager->getStorage(
      'heartbeat')->loadMultiple(
        $this->entityQuery->get(
          'heartbeat')
          ->condition('status', 1)
          ->condition('type', array_column($types, 'target_id'), 'IN')
          ->sort('created', 'DESC')
          ->range(0,25)

        ->execute());
  }

  /*
   * Load all available HeartbeatStream entities
   */
  public function getAllStreams() {
    return $this->entityTypeManager->getStorage('heartbeat_stream')->loadMultiple($this->loadAllEntities());
  }

  public function createStreamForUids($uids) {
    return $this->entityTypeManager->getStorage(
      'heartbeat')->loadMultiple(
        $this->entityQuery->get(
          'heartbeat')
          ->condition('status', 1)
          ->condition('uid', $uids, 'IN')
          ->sort('created', 'DESC')
          ->range(0,25)
        ->execute());
  }

//  public function createStreamForRecipient($uid) {
//    return $this->entityTypeManager->getStorage('heartbeat')->loadMultiple(
//      $this->entityQuery->get(
//        'heartbeat')
//      ->condition('status', 1)
//      ->orConditionGroup()->condition(
//      )
//    )
//  }

  public function createStreamByType($type) {
    $stream = $this->entityTypeManager->getStorage(
      'heartbeat_stream')->load(array_values($this->loadStream($type))[0]);
    if ($stream !== null) {
      $types = array();
      foreach ($stream->getTypes() as $heartbeatType) {
        $value = $heartbeatType->getValue()['target_id'];
        if ($value !== "0") {
          $types[] = $value;
        }
      }
      $beats = $this->entityTypeManager->getStorage(
        'heartbeat')->loadMultiple(
          $this->entityQuery->get('heartbeat')
            ->condition('status', 1)
            ->condition('type', $types, 'IN')
            ->sort('created', 'DESC')
            ->range(0,25)

          ->execute());

      if (count($beats) > 0) {
        $this->lastId = call_user_func('end', array_keys($beats));
        $this->configFactory->getEditable(
          'heartbeat_update_feed.settings')->set('lastId', $this->lastId)
                                           ->set('update', false)
                                           ->set('timestamp', array_values($beats)[0]->getRevisionCreationTime())
          ->save();

        return $beats;
      }
    }
    return null;
  }


  public function createStreamForUidsByType($uids, $type) {
    $stream = $this->entityTypeManager->getStorage('heartbeat_stream')->load(array_values($this->loadStream($type))[0]);
    if ($stream !== null) {
      $types = array();
      foreach ($stream->getTypes() as $heartbeatType) {
        $value = $heartbeatType->getValue()['target_id'];
        if ($value !== "0") {
          $types[] = $value;
        }
      }
      $beats = $this->entityTypeManager->getStorage(
        'heartbeat')->loadMultiple(
          $this->entityQuery->get(
            'heartbeat')
            ->condition('status', 1)
            ->condition('type', $types, 'IN')
            ->condition('uid', $uids, 'IN')
            ->sort('created', 'DESC')
            ->range(0,25)

          ->execute());

      if (count($beats) > 0) {
        $this->lastId = call_user_func('end', array_keys($beats));

        $this->configFactory->getEditable(
          'heartbeat_update_feed.settings')
          ->set('lastId', $this->lastId)
          ->set('update', false)
          ->set('timestamp', array_values($beats)[0]->getRevisionCreationTime())

        ->save();

        return $beats;
      }
    }
    return null;
  }

  public function getOlderStreamForUidsByType($uids, $type, $hid) {
    $stream = $this->entityTypeManager->getStorage('heartbeat_stream')->load(array_values($this->loadStream($type))[0]);
    if ($stream !== null) {
      $types = array();
      foreach ($stream->getTypes() as $heartbeatType) {
        $value = $heartbeatType->getValue()['target_id'];
        if ($value !== "0") {
          $types[] = $value;
        }
      }
      $beats = $this->entityTypeManager->getStorage(
        'heartbeat')->loadMultiple(
          $this->entityQuery->get(
            'heartbeat')
            ->condition('status', 1)
            ->condition('id', $hid, '<')
            ->condition('type', $types, 'IN')
            ->condition('uid', $uids, 'IN')
            ->sort('created', 'DESC')
            ->range(0,25)

          ->execute());

      if (count($beats) > 0) {
        $this->lastId = call_user_func('end', array_keys($beats));

        $this->configFactory->getEditable(
          'heartbeat_update_feed.settings')
          ->set('lastId', $this->lastId)
          ->set('update', false)
          ->set('timestamp', array_values($beats)[0]->getRevisionCreationTime())

        ->save();

        return $beats;
      }
    }
    return null;
  }


  public function createHashStreamForUidsByType($uids, $type, $tid) {
    $query = $this->database->query('
      SELECT id
      FROM heartbeat_field_revision hr
      INNER JOIN node n ON n.nid = hr.nid
      INNER JOIN node__field_tags fu ON fu.entity_id = n.nid 
      WHERE fu.field_tags_target_id = :tid', array(
        ':tid' => $tid
      )
    );
    $hids = array();
    foreach ($query->fetchAllKeyed() as $id => $row) {
      $hids[] = $id;
    }

    if (!empty($hids)) {
      $beats = $this->entityTypeManager->getStorage('heartbeat')
        ->loadMultiple(
          $this->entityQuery->get('heartbeat')
            ->condition('status', 1)
            ->condition('uid', $uids, 'IN')
            ->condition('id', $hids, 'IN')
            ->sort('created', 'DESC')

          ->execute());

      if (count($beats) > 0) {
        $this->lastId = call_user_func('end', array_keys($beats));

        $this->configFactory->getEditable('heartbeat_update_feed.settings')
          ->set('lastId', $this->lastId)
          ->set('update', FALSE)
          ->set('timestamp', array_values($beats)[0]->getRevisionCreationTime())
          ->save();

        return $beats;
      }
    }
    return null;
  }


  public function updateStreamForUidsByType($uids, $type) {
    $currentUid = \Drupal::currentUser()->id();
    $stream = $this->entityTypeManager->getStorage('heartbeat_stream')->load(array_values($this->loadStream($type))[0]);
    $uids[] = $currentUid;
    return $this->entityTypeManager->getStorage(
      'heartbeat')->loadMultiple(
        $this->entityQuery->get(
          'heartbeat')
          ->condition('status', 1)
          ->condition('revision_created', $this->latestTimestamp, '>')
          ->condition('type', array_column($stream->getTypes(), 'target_id'), 'IN')
          ->condition('uid', $uids, 'IN')
          ->sort('created', 'DESC')
        ->execute());
  }

  public function createUsernameStreamForUidsByType($uids, $feed, $tid) {
    $query = $this->database->query('
      SELECT coalesce(hr.id)
      FROM heartbeat_field_revision hr
      LEFT JOIN node__field_username un
      ON un.entity_id = hr.nid
      LEFT JOIN node__field_users u
      ON u.entity_id = hr.nid 
      WHERE u.field_users_target_id = :tid
      OR un.field_username_target_id = :tid', array(
        ':tid' => $tid
      )
    );
    $hids = array();
    foreach ($query->fetchAllKeyed() as $id => $row) {
      $hids[] = $id;
    }

    if (!empty($hids)) {
      $beats = $this->entityTypeManager->getStorage('heartbeat')
        ->loadMultiple(
          $this->entityQuery->get('heartbeat')
            ->condition('status', 1)
            ->condition('uid', $uids, 'IN')
            ->condition('id', $hids, 'IN')
            ->sort('created', 'DESC')

            ->execute());

      if (count($beats) > 0) {
        $this->lastId = call_user_func('end', array_keys($beats));

        $this->configFactory->getEditable('heartbeat_update_feed.settings')
          ->set('lastId', $this->lastId)
          ->set('update', FALSE)
          ->set('timestamp', array_values($beats)[0]->getRevisionCreationTime())
          ->save();

        return $beats;
      }
    }
    return null;

  }

}
