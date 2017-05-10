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
   * @var Drupal\heartbeat8\HeartbeatTypeServices
   */
  protected $heartbeat8_heartbeattype;

  /**
   * Drupal\heartbeat8\HeartbeatStreamServices definition.
   *
   * @var Drupal\heartbeat8\HeartbeatStreamServices
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

    $streamEntity = $this->heartbeatstream->getEntityById(1);
    $types = $streamEntity->get('types');
    $i = 1;
    foreach($types->getValue() as $heartbeatType) {
      $arg .= '   ' . $i . '. ' . $heartbeatType['target_id'];
      $i++;
    }
    $emptyVariable = 'not empty';

    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: start with parameter(s): ' . $arg),
    ];
  }

}
