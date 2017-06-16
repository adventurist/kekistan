<?php

namespace Drupal\heartbeat\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\flag\FlagService;
use Drupal\User\Entity\User;
use Drupal\Flag\Entity\Flag;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\file\Entity\File;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Database;
use Drupal\heartbeat\HeartbeatTypeServices;
use Drupal\heartbeat\HeartbeatStreamServices;
use Drupal\heartbeat\HeartbeatService;

//*  deriver = "Drupal\heartbeat\Plugin\Derivative\HeartbeatBlockDeriver

/**
 * Provides a 'HeartbeatBlock' block.
 *
 * @Block(
 *  id = "heartbeat_block",
 *  admin_label = @Translation("Heartbeat block"),
 * )
 */
class HeartbeatBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\heartbeat\HeartbeatTypeServices definition.
   *
   * @var \Drupal\heartbeat\HeartbeatTypeServices
   */
  protected $heartbeatTypeServices;
  /**
   * Drupal\heartbeat\HeartbeatStreamServices definition.
   *
   * @var \Drupal\heartbeat\HeartbeatStreamServices
   */
  protected $heartbeatStreamServices;
  /**
   * Drupal\heartbeat\HeartbeatService definition.
   *
   * @var \Drupal\heartbeat\HeartbeatService
   */
  protected $heartbeatService;

  protected $entityTypeManager;

  protected $dateFormatter;

  protected $flagService;
  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        HeartbeatTypeServices $heartbeat_heartbeattype,
	HeartbeatStreamServices $heartbeatstream,
	HeartbeatService $heartbeat, EntityTypeManager $entity_type_manager, DateFormatter $date_formatter, FlagService $flag_service
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->heartbeatTypeServices = $heartbeat_heartbeattype;
    $this->heartbeatStreamServices = $heartbeatstream;
    $this->heartbeatService = $heartbeat;
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
    $this->flagService = $flag_service;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('heartbeat.heartbeattype'),
      $container->get('heartbeatstream'),
      $container->get('heartbeat'),
      $container->get('entity_type.manager'),
      $container->get('date.formatter'),
      $container->get('flag')
    );
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Database\InvalidQueryException
   */
  public function build() {

    $myConfig = \Drupal::service('config.factory')->getEditable('heartbeat_feed.settings');
    $friendData = \Drupal::config('heartbeat_friendship.settings')->get('data');

    $feed = $myConfig->get('message');
    $uids = null;
    $messages = array();

    $query = Database::getConnection()->select('heartbeat_friendship', 'hf')
      ->fields('hf',['uid', 'uid_target']);
    $conditionOr = $query->orConditionGroup()
      ->condition('hf.uid', \Drupal::currentUser()->id())
      ->condition('hf.uid_target', \Drupal::currentUser()->id());

    $results = $query->condition($conditionOr)->execute();
    if ($result = $results->fetchAll()) {
      $uids = array();
      foreach ($result as $uid) {
        $uids[] = $uid->uid_target;
        $uids[] = $uid->uid;
      }
    }
      if ($feed !== null) {
      $uids = count($uids) > 1 ? array_unique($uids) : $uids;
        if (!empty($uids)) {
          foreach ($this->heartbeatStreamServices->createStreamForUidsByType($uids, $feed) as $heartbeat) {
            $this->renderMessage($messages, $heartbeat);
          }
        } else {
          foreach ($this->heartbeatStreamServices->createStreamByType($feed) as $heartbeat) {
            $this->renderMessage($messages, $heartbeat);
          }
        }
      } else {
//        foreach ($this->heartbeatStreamServices->createStreamForUids($uids) as $heartbeat) {
        foreach ($this->heartbeatStreamServices->loadAllStreams() as $heartbeat) {
          $this->renderMessage($messages, $heartbeat);
        }
      }

      return [
        '#theme' => 'heartbeat_stream',
        '#messages' => $messages,
        '#attached' => array(
          'library' => 'heartbeat/heartbeat',
          'drupalSettings' => [
            'activeFeed' => 'jigga',
            'friendData' => $friendData,
          ]
        ),
        '#cache' => array('max-age' => 0)
      ];

    }

    private function renderMessage(array &$messages, $heartbeat) {

      $timeago = $this->dateFormatter->formatInterval(REQUEST_TIME - $heartbeat->getCreatedTime());
      $user = $heartbeat->getOwner();
      $flag = $this->flagService->getFlagById("friendship");
      $flagLink = $flag->getLinkTypePlugin()->getAsLink($flag, $user);
      $flagUrl = $flagLink->getUrl()->toString();
      $flagText = $flagLink->getText();
      $profilePic = $user->get('user_picture')->getValue()[0]['target_id'];
      $flagRenderable = $flagLink->toRenderable();
      $renderArray = $flagRenderable['#url']->toRenderArray();

      if ($profilePic === null) {
        $profilePic = 86;
      }

      $pic = File::load($profilePic);

      if ($pic !== null) {
        $style = $this->entityTypeManager->getStorage('image_style')->load('thumbnail');

        $rendered = $style->buildUrl($pic->getFileUri());
      }


//TODO GET ACTION AND APPEND TO CLASSES IN FLAG WRAPPER

      $messages[] = array('heartbeat' => $heartbeat->getMessage()->getValue()[0]['value'],
        'userPicture' => $rendered,
        'userId' => $user->id(),
        'timeAgo' => $timeago,
        'friendFlag' => $flagUrl,
        'friendFlagText' => $flagText,
        'flagId' => $flag->id(),
        'userId' => $user->id(),
        );
    }
}
