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
   */
  public function flag_entity_flagged(Event $event) {
    $stophere = null;
    $flagging = $event->getFlagging();
    if ($flagging->getFlagId() == 'friendship') {
      $entity = $this->flagService->getFlagById($flagging->getFlagId());

      $user = $flagging->getOwner();


      if ($entity->id() && $user->isAuthenticated()) {

        $heartbeatTypeService = \Drupal::service('heartbeat.heartbeattype');
        $tokenService = \Drupal::service('token');

        foreach ($heartbeatTypeService->getTypes() as $type) {

          $heartbeatTypeEntity = $heartbeatTypeService->load($type);

          if ($heartbeatTypeEntity->getMainEntity() == "flagging") {
            $arguments = json_decode($heartbeatTypeEntity->getArguments());
//            $entityTypeFlagging = $flagging->getEntityType();
//            $id = $entityTypeFlagging->id();
//            $id_other = $flagging->get('entity_id');
//            $entityTypeId = $flagging->getEntityTypeId();
//            $flaggable = $flagging->getFlaggableId();
            $user2 = User::load($flagging->getFlaggableId());


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

            //      $translatedMessage = t($messageTemplate);

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
