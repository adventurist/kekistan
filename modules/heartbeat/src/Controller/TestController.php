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
$jigga = 'what';

    $stuff = file_get_contents('http://cointrx.com:6969/prices/latest');

    $decoded = \json_decode($stuff);


    return [
      '#type' => 'markup',
      '#markup' => $this->t('jizzla'),
    ];


  }

  public function getHeartbeats() {

    $data = file_get_contents("public://heartbeats.dat");
    $heartbeats = unserialize($data);
    $errors = false;
    if (is_array($heartbeats)) {
      $heartbeats = array_reverse($heartbeats);
      foreach ($heartbeats as $heartbeat) {

        if ($heartbeat instanceof \Drupal\heartbeat\Entity\Heartbeat) {
//          try {
//            $heartbeat->save();
//          } catch (\Exception $e) {
//            $message = $e->getMessage();
//          }
//        }
          $nid = $heartbeat->get('nid')->getValue()[0]['target_id'];
          $title = 'Dev Test';
          $type = $type = $this->heartbeat_heartbeattype->load($heartbeat->get('type')->getValue()[0]['target_id']);

          if ($type->get('mainentity') == 'node') {
            $node = $this->entityTypeManager()->getStorage('node')->load($nid);
            if ($node !== null) {
              $title = $node->getTitle();
            }
          }


          $heartbeatActivity = Heartbeat::create([
            'uid' => $heartbeat->getOwnerId(),
            'nid' => $heartbeat->getNid()->getValue()[0]['target_id'],
            'name' => $title,
            'type' => $heartbeat->getType(),
            'message' => $heartbeat->getMessage()->getValue()[0]['value']
          ]);

          if (!$heartbeatActivity->save()) {
            $errors = true;
          }
        }
      }
    }
    $result = $errors ? 'Error restoring Heartbeats' : 'Heartbeats restored';

    return [
      '#type' => 'markup',
      '#markup' => $this->t($result),
    ];

  }
//
  public function deleteHeartbeats() {
    $entities = \Drupal::service("entity.query")->get("heartbeat")->execute();
    foreach($entities as $entity) {
      $heartbeat = \Drupal::service("entity_type.manager")->getStorage("heartbeat")->load($entity);
      $heartbeat->delete();
    }

    return [
      '#type' => 'markup',
      '#markup' => $this->t('Deleting them Heartbeats')
    ];
  }

}
