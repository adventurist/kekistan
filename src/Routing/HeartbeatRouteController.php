<?php

namespace Drupal\heartbeat\Routing;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Drupal\heartbeat\HeartbeatStream;
use Drupal\heartbeat\HeartbeatService;
use Drupal\heartbeat\HeartbeatStreamServices;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Class HeartbeatRouteController.
 *
 * @package Drupal\heartbeat\Controller
 */
class HeartbeatRouteController extends ControllerBase {

  /**
   * Drupal\heartbeat\HeartbeatService definition.
   *
   * @var \Drupal\heartbeat\HeartbeatService
   */
  protected $heartbeatService;
  /**
   * Drupal\heartbeat\HeartbeatStreamServices definition.
   *
   * @var \Drupal\heartbeat\HeartbeatStreamServices
   */
  protected $heartbeatStreamService;
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
   * {@inheritdoc}
   */
  public function __construct(HeartbeatService $heartbeatService, HeartbeatStreamServices $heartbeatStreamService, QueryFactory $entityQuery, EntityTypeManager $entityTypeManager) {
    $this->heartbeatService = $heartbeatService;
    $this->heartbeatStreamService = $heartbeatStreamService;
    $this->entityQuery = $entityQuery;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('heartbeat'),
      $container->get('heartbeatstream'),
      $container->get('entity.query'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Getroutes.
   *
   * @return string
   *   Return Hello string.
   */
  public function getRoutes() {
    \Drupal::logger('HeartbeatRouteController')->debug('We getting called, yo');

    $routeCollection = new RouteCollection();

    foreach ($this->heartbeatStreamService->getAllStreams() as $heartbeatStream) {
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
      // Add the route under the name 'example.content'.
      $routeCollection->add('heartbeat.' . $heartbeatStream->getName(), $route);
    }
    \Drupal::logger('HeartbeatRouteController')->debug('Data is %data', array('%data' => $routeCollection));
    return $routeCollection;

  }
}
