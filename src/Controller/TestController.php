<?php

namespace Drupal\heartbeat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\heartbeat\HeartbeatTypeServices;
use Drupal\heartbeat\HeartbeatStreamServices;

/**
 * Class TestController.
 *
 * @package Drupal\heartbeat\Controller
 */
class TestController extends ControllerBase {

  /**
   * Drupal\heartbeat\HeartbeatTypeServices definition.
   *
   * @var HeartbeatTypeServices
   */
  protected $heartbeat_heartbeattype;

  /**
   * Drupal\heartbeat\HeartbeatStreamServices definition.
   *
   * @var HeartbeatStreamServices
   */
  protected $heartbeatstream;
  /**
   * {@inheritdoc}
   */
  public function __construct(HeartbeatTypeServices $heartbeat_heartbeattype, HeartbeatStreamServices $heartbeatstream) {
    $this->heartbeat_heartbeattype = $heartbeat_heartbeattype;
    $this->heartbeatstream = $heartbeatstream;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('heartbeat.heartbeattype'),
      $container->get('heartbeatstream')
    );
  }

  /**
   * Start.
   *
   * @return string
   *   Return Hello string.
   */
  public function start($arg) {

    $node = \Drupal\node\Entity\Node::load(186);

    $streamEntities = $this->heartbeatstream->loadAllEntities();

    foreach ($streamEntities as $streamEntityId) {

      $streamEntity = $this->heartbeatstream->getEntityById($streamEntityId);
      $types = $streamEntity->get('types');
      $arg .= 'Stream::   ' . $streamEntity->id();

      $i = 1;

      foreach ($types->getValue() as $heartbeatType) {
        $arg .= '   ' . $i . '. ' . $heartbeatType['target_id'];
        $i++;
      }
    }

    $heartbeatTypeService = \Drupal::service('heartbeat.heartbeattype');

    $entityBundleInfo = $node->bundle();
    $entityType = $node->getEntityType();
    $availableBundles = $heartbeatTypeService->getEntityBundles($node->getEntityType());

    foreach ($heartbeatTypeService->getTypes() as $type) {
      $heartbeatTypeEntity = \Drupal::entityTypeManager()->getStorage('heartbeat_type')->load($type);
    }
    $emptyVariable = 'not empty';

    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: start with parameter(s): ' . $arg),
    ];
  }

}
