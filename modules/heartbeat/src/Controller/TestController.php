<?php

namespace Drupal\heartbeat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\flag\FlagService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\heartbeat\HeartbeatTypeServices;
use Drupal\heartbeat\HeartbeatStreamServices;
use Drupal\statusmessage\StatusTwitter;
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
  public function __construct(HeartbeatTypeServices $heartbeat_heartbeattype, HeartbeatStreamServices $heartbeatstream, FlagService $flag_service, EntityTypeManager $entity_type_manager) {
    $this->heartbeat_heartbeattype = $heartbeat_heartbeattype;
    $this->heartbeatStream = $heartbeatstream;
    $this->flagService = $flag_service;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('heartbeat.heartbeattype'),
      $container->get('heartbeatstream'),
      $container->get('flag'),
      $container->get('entity_type.manager')

    );
  }

  /**
   * Start.
   *
   * @return string
   * @throws \InvalidArgumentException
   * @throws \Drupal\Core\Database\IntegrityConstraintViolationException
   * @throws \Drupal\Core\Database\DatabaseExceptionWrapper
   * @throws \Drupal\Core\Database\InvalidQueryException
   *   Return Hello string.
   */
  public function start($arg) {

    $statusTwitter = new StatusTwitter('https://twitter.com/lvd_drm/status/874429014684745728');
    $nid = $statusTwitter->sendRequest();

    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: start with parameter(s): ' . $arg),
    ];
  }

}
