<?php

namespace Drupal\heartbeat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\flag\FlagService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\heartbeat\HeartbeatTypeServices;
use Drupal\heartbeat\HeartbeatStreamServices;
use Drupal\heartbeat\Entity\Heartbeat;
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

  protected $entityQuery;
  /**
   * {@inheritdoc}
   */
public function __construct(HeartbeatTypeServices $heartbeat_heartbeattype, HeartbeatStreamServices $heartbeatstream, FlagService $flag_service, EntityTypeManager $entity_type_manager, QueryFactory $entity_query) {
    $this->heartbeat_heartbeattype = $heartbeat_heartbeattype;
    $this->heartbeatStream = $heartbeatstream;
    $this->flagService = $flag_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('heartbeat.heartbeattype'),
      $container->get('heartbeatstream'),
      $container->get('flag'),
      $container->get('entity_type.manager'),
      $container->get('entity.query')
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

  public function saveHeartbeats() {
    $heartbeats = $this->entityQuery->get("heartbeat")->execute();
    $data = array();
    foreach ($heartbeats as $hid) {
      $beat = $this->entityTypeManager->getStorage("heartbeat")->load($hid);
      $data[] = $beat;
    }

    $result = '';

     $result = file_put_contents("public://heartbeats.dat", serialize($data)) ? 'Saved Heartbeats' : 'Error saving heartbeats';

    return [
      '#type' => 'markup',
      '#markup' => $this->t($result),
    ];


  }

  public function getHeartbeats() {

    $data = file_get_contents("public://heartbeats.dat");
    $heartbeats = unserialize($data);
    $errors = false;
    if (is_array($heartbeats)) {
      $heartbeats = array_reverse($heartbeats);
      foreach ($heartbeats as $heartbeat) {

        $message = $heartbeat->getMessage();
        $heartbeatActivity = Heartbeat::create([
          'type' => $heartbeat->id(),
          'uid' => $heartbeat->getOwnerId(),
          'nid' => $heartbeat->getNid()->getValue()[0]['target_id'],
          'name' => 'Dev Test',
          'type' => $heartbeat->getType(),
          'message' => $heartbeat->getMessage()->getValue()[0]['value']
        ]);

        if (!$heartbeatActivity->save()) {
          $errors = true;
        }
      }
    }
    $result = $errors ? 'Error restoring Heartbeats' : 'Heartbeats restored';

    return [
      '#type' => 'markup',
      '#markup' => $this->t($result),
    ];

  }

  public function deleteHeartbeats() {
    $entities = \Drupal::service("entity.query")->get("heartbeat")->execute();
    foreach($entities as $entity) {
      $heartbeat = \Drupal::service("entity_type.manager")->getStorage("heartbeat")->load($entity);
      $heartbeat->delete();
    }
  }

}
