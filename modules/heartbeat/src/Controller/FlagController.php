<?php

namespace Drupal\heartbeat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\flag\FlagService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FlagController.
 *
 * @package Drupal\heartbeat\Controller
 */
class FlagController extends ControllerBase {

  /**
   * Drupal\flag\FlagService definition.
   *
   * @var Drupal\flag\FlagService
   */
  protected $flagService;
  protected $entityTypeManager;
  protected $entityQuery;
  /**
   * {@inheritdoc}
   */
  public function __construct(FlagService $flag, EntityTypeManager $entity_type_manager, QueryFactory $entity_query) {
    $this->flagService = $flag;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('flag'),
      $container->get('entity_type.manager'),
      $container->get('entity.query')
    );
  }

  /**
   * Hello.
   *
   * @return string
   *   Return Hello string.
   */
  public function getUserFlaggings() {

    $entity_type = \Drupal::request()->request->get('entity_type');
    $entity_id = \Drupal::request()->request->get('entity_id');
    $flag_id = \Drupal::request()->request->get('flag_id');
    $uid = \Drupal::request()->request->get('uid');;
    $flaggedByUser = $this->entityQuery->get("flagging")
      ->condition("flag_id", $flag_id, "=")
      ->condition("entity_type", $entity_type, "=")
      ->condition("entity_id", $entity_id)
      ->condition("uid", $uid, "=")
      ->execute();
    $response = new Response();
    $response->setContent(json_encode(array(
      'flaggedByUser' => count(
        $this->entityQuery->get("flagging")
          ->condition("flag_id", $flag_id, "=")
          ->condition("entity_type", $entity_type, "=")
          ->condition("entity_id", $entity_id)
          ->condition("uid", $uid, "=")
            ->execute()) > 0)
    ));

    $response->headers->set('Content-Type', 'application/json');
    return $response;
  }

}
