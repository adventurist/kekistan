<?php

namespace Drupal\heartbeat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\heartbeat\HeartbeatService;
use Drupal\heartbeat\HeartbeatStreamServices;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Class StreamTestController.
 *
 * @package Drupal\heartbeat\Controller
 */
class StreamTestController extends ControllerBase {

  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;
  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * \Drupal\heartbeat\HeartbeatStreamServices definition.
   *
   * @var \Drupal\heartbeat\HeartbeatStreamServices
   */
  protected $heartbeatStreamService;

  /**
   * \Drupal\heartbeat\HeartbeatService definition.
   *
   * @var \Drupal\heartbeat\HeartbeatService
   */
  protected $heartbeatService;

  /**
   * {@inheritdoc}
   */
  public function __construct(QueryFactory $entity_query, EntityTypeManager $entity_type_manager, HeartbeatStreamServices $heartbeatStreamService, HeartbeatService $heartbeatService) {
    $this->entityQuery = $entity_query;
    $this->entityTypeManager = $entity_type_manager;
    $this->heartbeatStreamService = $heartbeatStreamService;
    $this->heartbeatService = $heartbeatService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('entity_type.manager'),
      $container->get('heartbeatstream'),
      $container->get('heartbeat')
    );
  }

  /**
   * Stream.
   *
   * @return string
   *   Return Hello string.
   */
  public function stream() {
    $messages = array();
    $types = $this->heartbeatStreamService->getTypesById(1);
    foreach ($types as $type) {
      if ($type != null) {

        $heartbeatType = $type->getValue();

        $heartbeats = $this->heartbeatService->loadByType($heartbeatType);

        foreach($heartbeats as $heartbeat) {
          $messages[] = $heartbeat->getMessage()->getValue()[0]['value'];
        }
      }
    }

    return [
      '#theme' => 'heartbeat_stream',
      '#messages' => array_reverse($messages),
    ];
  }

}
