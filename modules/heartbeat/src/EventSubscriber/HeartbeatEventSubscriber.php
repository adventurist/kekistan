<?php

namespace Drupal\heartbeat\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\Database\Database;
use Drupal\flag\FlagService;
use Drupal\flag\Entity\Flagging;
use Drupal\user\Entity\User;
use Drupal\heartbeat\Entity\Heartbeat;
use Drupal\heartbeat\HeartbeatTypeService;
use Drupal\heartbeat\HeartbeatStreamServices;
use Drupal\heartbeat\HeartbeatService;

const NOT_FRIEND = -1;
const PENDING = 0;
const FRIEND = 1;

/**
 * Class HeartbeatEventSubscriber.
 *
 * @package Drupal\heartbeat
 */
class HeartbeatEventSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\flag\FlagService definition.
   *
   * @var \Drupal\flag\FlagService
   */
  protected $flagService;
  /**
   * Drupal\heartbeat\HeartbeatTypeService definition.
   *
   * @var \Drupal\heartbeat\HeartbeatTypeService
   */
  protected $heartbeatTypeService;
  /**
   * Drupal\heartbeat\HeartbeatStreamServices definition.
   *
   * @var \Drupal\heartbeat\HeartbeatStreamServices
   */
  protected $heartbeatStreamService;
  /**
   * Drupal\heartbeat\HeartbeatService definition.
   *
   * @var \Drupal\heartbeat\HeartbeatService
   */
  protected $heartbeatService;

  /**
   * Constructor.
   */
  public function __construct(FlagService $flag, HeartbeatTypeService $heartbeat_heartbeattype, HeartbeatStreamServices $heartbeatstream, HeartbeatService $heartbeat) {
    $this->flagService = $flag;
    $this->heartbeatTypeService = $heartbeat_heartbeattype;
    $this->heartbeatStreamService = $heartbeatstream;
    $this->heartbeatService = $heartbeat;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['flag.entity_flagged'] = ['flag_entity_flagged'];
    $events['flag.entity_unflagged'] = ['flag_entity_unflagged'];

    return $events;
  }

  /**
   * This method is called whenever the flag.entity_flagged event is
   * dispatched.
   *
   * @param GetResponseEvent $event
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function flag_entity_flagged(Event $event) {
    $friendStatus = NOT_FRIEND;
    $flagging = $event->getFlagging();

    if ($flagging instanceof Flagging) {

      $flagId = $flagging->getFlagId();

      if ($flagId === 'friendship') {
        $entity = $this->flagService->getFlagById($flagging->getFlagId());

        $user = $flagging->getOwner();

        if ($entity->id() && $user->isAuthenticated()) {

          $heartbeatTypeService = \Drupal::service('heartbeat.heartbeattype');
          $tokenService = \Drupal::service('token');

          foreach ($heartbeatTypeService->getTypes() as $type) {

            $heartbeatTypeEntity = $heartbeatTypeService->load($type);

            if ($heartbeatTypeEntity->getMainEntity() === "flagging") {

              $arguments = json_decode($heartbeatTypeEntity->getArguments());
              $user2 = User::load($flagging->getFlaggableId());
              $targetUserFriendships = $this->flagService->getFlagFlaggings($entity, $user2);

              foreach ($targetUserFriendships as $friendship) {
                if ($friendship->getFlaggableId() === $user->id()) {
                  $friendStatus = FRIEND;
                  break;
                }
              }

              $friendStatus = $friendStatus == FRIEND ? FRIEND : PENDING;

              foreach ($arguments as $key => $argument) {
                $variables[$key] = $argument;
              }

              Heartbeat::updateFriendship($user->id(), $user2->id(), time(), $friendStatus);

              $preparsedMessageString = strtr($heartbeatTypeEntity->getMessage(), $variables);
              $entitiesObj = new \stdClass();
              $entitiesObj->type = 'user';
              $entitiesObj->entities = [$user, $user2];
              $entities = array(
                'flagging' => $entity,
                'user' => $entitiesObj,
              );

              $heartbeatMessage = Heartbeat::buildMessage($tokenService, $preparsedMessageString, $entities, $entity->getEntityTypeId(), null);

              $heartbeatActivity = Heartbeat::create([
                'type' => $heartbeatTypeEntity->id(),
                'uid' => $user->id(),
                'nid' => $entity->id(),
                'name' => 'Dev Test',
              ]);

              $heartbeatActivity->setMessage($heartbeatMessage);
              $heartbeatActivity->save();

            }
          }
        }
        $friendships = Database::getConnection()->select("heartbeat_friendship", "hf")
          ->fields('hf', array('status', 'uid', 'uid_target'))
          ->execute();

        $friendData = $friendships->fetchAll();

        $friendConfig = \Drupal::configFactory()->getEditable('heartbeat_friendship.settings');

        $friendConfig->set('data', json_encode($friendData))->save();
      }
//      if ($flagging->getFlaggableType() === 'heartbeat') {
//        $oppositeFlag = null;
//        if ($flagging->getFlagId() === 'heartbeat_like') {
//          $oppositeFlag = $this->flagService->getFlagById('heartbeat_unlike');
//        } elseif ($flagging->getFlagId() === 'heartbeat_unlike') {
//          $oppositeFlag = $this->flagService->getFlagById('heartbeat_unlike');
//        }
//        if ($oppositeFlag) {
//          $entity = $flagging->getFlaggable();
//          $user = \Drupal::currentUser()->getAccount();
//          $oppositeFlagging = $this->flagService->getFlagging($oppositeFlag, $entity, $user);
//          if ($oppositeFlagging) {
//            $this->flagService->unflag($oppositeFlag, $entity, $user);
//          }
//        }
//      }
    }
  }

  /**
   * This method is called whenever the flag.entity_unflagged event is
   * dispatched.
   *
   * @param GetResponseEvent $event
   */
  public function flag_entity_unflagged(Event $event) {

    $friendStatus = FRIEND;
    $flagging = array_values($event->getFlaggings())[0];

    if ($flagging->getFlagId() === 'friendship') {
      $entity = $this->flagService->getFlagById($flagging->getFlagId());

      $user = $flagging->getOwner();

      if ($entity->id() && $user->isAuthenticated()) {

        $heartbeatTypeService = \Drupal::service('heartbeat.heartbeattype');
        $tokenService = \Drupal::service('token');

        foreach ($heartbeatTypeService->getTypes() as $type) {

          $heartbeatTypeEntity = $heartbeatTypeService->load($type);

          if ($heartbeatTypeEntity->getMainEntity() === "flagging") {

            $arguments = json_decode($heartbeatTypeEntity->getArguments());
            $user2 = User::load($flagging->getFlaggableId());
            $targetUserFriendships = $this->flagService->getFlagFlaggings($entity, $user2);

            foreach ($targetUserFriendships as $friendship) {
              if ($friendship->getFlaggableId() === $user->id()) {
                $friendStatus = NOT_FRIEND;
                break;
              }
            }

            $friendStatus = $friendStatus == NOT_FRIEND ? NOT_FRIEND : PENDING;

            foreach ($arguments as $key => $argument) {
              $variables[$key] = $argument;
            }

            Heartbeat::updateFriendship($user->id(), $user2->id(), time(), $friendStatus);

          }
        }

      }
    }
    $friendships = Database::getConnection()->select("heartbeat_friendship", "hf")
      ->fields('hf', array('status', 'uid', 'uid_target'))
      ->execute();

    $friendData = $friendships->fetchAll();

    $friendConfig = \Drupal::configFactory()->getEditable('heartbeat_friendship.settings');

    $friendConfig->set('data', json_encode($friendData))->save();
  }
}
