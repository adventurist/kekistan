<?php

namespace Drupal\heartbeat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\heartbeat\HeartbeatTypeServices;
use Drupal\heartbeat\HeartbeatStreamServices;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


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
  protected $heartbeatStream;
  /**
   * {@inheritdoc}
   */
  public function __construct(HeartbeatTypeServices $heartbeat_heartbeattype, HeartbeatStreamServices $heartbeatstream) {
    $this->heartbeat_heartbeattype = $heartbeat_heartbeattype;
    $this->heartbeatStream = $heartbeatstream;
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
    foreach ($this->heartbeatStream->getAllStreams() as $heartbeatStream) {

      $route = new Route(
        $heartbeatStream->getPath()->getValue()[0]['value'],

        array(
          '_controller' => '\Drupal\heartbeat\Controller\HeartbeatStreamController::createRoute',
          '_title' => $heartbeatStream->getName(),
          'heartbeatStreamId' => $heartbeatStream->id(),
        ),
        array(
          '_permission'  => 'access content',
        )
      );

    }
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: start with parameter(s): ' . $arg),
    ];
  }

}
