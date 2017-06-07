<?php

namespace Drupal\heartbeat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\flag\FlagService;
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

    $flag = $this->flagService->getFlagById('friendship');

    $friendships = Database::getConnection()->select("heartbeat_friendship", "hf")
      ->fields('hf', array('status', 'uid', 'uid_target'))
      ->execute();

    foreach ($friendships->fetchAll() as $friendship) {
      $revFriendship = Database::getConnection()->select('heartbeat_friendship', 'hf')
        ->fields('hf', array('status'))
        ->condition('uid', $friendship->uid_target)
        ->condition('uid_target', $friendship->uid)
        ->execute();

      $revFriendResult = $revFriendship->fetchField();

      if ($revFriendResult > -2) {
        if ($revFriendResult !== $friendship->status) {
          $update = Database::getConnection()->update('heartbeat_friendship')
            ->fields(array(
                ':status' => 1,
              )
            )
            ->condition('uid', $friendship->uid)
            ->condition('uid_target', $friendship->uid_target);
          if ($updated = !$update->execute()) {
            \Drupal::logger('Heartbeat Cron')->error('Could not update status for friendship');
          }
        }

        if ($revFriendResult === $friendship->status ||
        $updated) {

          $userEntity = $this->entityTypeManager->getStorage('user')->load($friendship->uid);
          $userTargetEntity = $this->entityTypeManager->getStorage('user')->load($friendship->uid_target);
          $flaggingFound = false;

          foreach ($this->flagService->getEntityFlaggings($flag, $userTargetEntity) as $flagging) {
            $flOwner = $flagging->getOwnerId();
            $usId = $userEntity->id();
            $flaggableId = $flagging->getFlaggableId();
            //TODO ownerId and entity Id seem to be reversed.

            if ($flagging->getOwnerId() == $userEntity->id() && $flagging->getFlaggableId() == $friendship->uid_target) {
              $flaggingFound = true;
              break;
            }
          }

          if (!$flaggingFound) {
            $flagging = $this->flagService->flag($flag, $userTargetEntity, $userEntity);
          }

          $flaggingReverseFound = false;

          foreach ($this->flagService->getEntityFlaggings($flag, $userEntity) as $flagging) {
            if ($flagging->getOwnerId() == $userTargetEntity->id() && $flagging->getFlaggableId() == $friendship->uid) {
              $flaggingReverseFound = true;
              break;
            }
          }

          if (!$flaggingReverseFound) {
            $flagging = $this->flagService->flag($flag, $userEntity, $userTargetEntity);
          }
          //TODO update flagging values or create flaggings

        }
      } else if ($friendship->status === 1) {
        //TODO Add reverse friendship
        $insertReverse = Database::getConnection()->insert('heartbeat_friendship')
          ->fields([
            'uid' => $friendship->uid_target,
            'uid_target' => $friendship->uid,
            'created' => time(),
            'status' => 1
          ]);

        if ($insertReverse->execute()) {

          if ($friendship->status < 1) {
            $updateFriendship = Database::getConnection()->update('heartbeat_friendship')
              ->fields(array(
                'status' => 1,
              ))
              ->condition('uid', $friendship->uid)
              ->condition('uid_target', $friendship->uid_target);
            if (!$updateFriendship->execute()) {
              \Drupal::logger('Friendship update failed');
            }
          }
        } else {
          \Drupal::logger('Heartbeat')->debug('Unable to insert or update for User with ID %id', ['%id' => $friendship->uid]);
        }
      } else {
        //TODO figure out how to set friendship pending
      }
    }

    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: start with parameter(s): ' . $arg),
    ];
  }

}
