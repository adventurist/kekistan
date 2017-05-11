<?php

namespace Drupal\heartbeat8\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\heartbeat8\HeartbeatTypeServices;
use Drupal\heartbeat8\HeartbeatStreamServices;

/**
 * Class TestController.
 *
 * @package Drupal\heartbeat8\Controller
 */
class TestController extends ControllerBase {

  /**
   * Drupal\heartbeat8\HeartbeatTypeServices definition.
   *
   * @var HeartbeatTypeServices
   */
  protected $heartbeat8_heartbeattype;

  /**
   * Drupal\heartbeat8\HeartbeatStreamServices definition.
   *
   * @var HeartbeatStreamServices
   */
  protected $heartbeatstream;
  /**
   * {@inheritdoc}
   */
  public function __construct(HeartbeatTypeServices $heartbeat8_heartbeattype, HeartbeatStreamServices $heartbeatstream) {
    $this->heartbeat8_heartbeattype = $heartbeat8_heartbeattype;
    $this->heartbeatstream = $heartbeatstream;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('heartbeat8.heartbeattype'),
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

    $streamEntities = $this->heartbeatstream->loadAllEntities()->execute();

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

    $heartbeatTypeService = \Drupal::service('heartbeat8.heartbeattype');
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
