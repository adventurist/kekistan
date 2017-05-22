<?php

namespace Drupal\heartbeat\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\flag\FlagService;
use Drupal\user\Entity\User;
use Drupal\heartbeat\Entity\Heartbeat;
use Drupal\heartbeat\HeartbeatTypeServices;
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
   * Drupal\heartbeat\HeartbeatTypeServices definition.
   *
   * @var \Drupal\heartbeat\HeartbeatTypeServices
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
  public function __construct(FlagService $flag, HeartbeatTypeServices $heartbeat_heartbeattype, HeartbeatStreamServices $heartbeatstream, HeartbeatService $heartbeat) {
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
   * @return GetResponseEvent|Event
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function flag_entity_flagged(Event $event) {
    $friendStatus = NOT_FRIEND;
    $flagging = $event->getFlagging();
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
                $friendStatus = FRIEND;
                break;
              }
            }

            $friendStatus = $friendStatus == FRIEND ? FRIEND : PENDING;

            if ($friendStatus === FRIEND) {
              drupal_set_message($user->getUsername() . ' is now friends with ' . $user2->getUsername());
            } else if ($friendStatus === PENDING) {
              drupal_set_message($user->getUsername() . ' has requested friendship with ' . $user2->getUsername());
            } else {
              drupal_set_message($user->getUsername() . ' is unable to request friendship with ' . $user2->getUsername());
            }

            foreach ($arguments as $key => $argument) {
              $variables[$key] = $argument;
            }

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
    }

    drupal_set_message('Event flag.entity_flagged thrown by Subscriber in module heartbeat.', 'status', TRUE);
    return $event;
  }

  /**
   * This method is called whenever the flag.entity_unflagged event is
   * dispatched.
   *
   * @param GetResponseEvent $event
   */
  public function flag_entity_unflagged(Event $event) {
    drupal_set_message('Event flag.entity_unflagged thrown by Subscriber in module heartbeat.', 'status', TRUE);
  }

}
