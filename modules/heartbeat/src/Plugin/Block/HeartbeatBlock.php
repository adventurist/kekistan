<?php

namespace Drupal\heartbeat\Plugin\Block;

use Drupal\heartbeat\Entity\Heartbeat;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\flag\FlagService;
use Drupal\comment\Entity\Comment;
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
use Drupal\heartbeat\Plugin\Block\HeartbeatCommentBlock;

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

  protected $formBuilder;

  protected $configFactory;

  protected $timestamp;

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
	HeartbeatService $heartbeat, EntityTypeManager $entity_type_manager, DateFormatter $date_formatter, FlagService $flag_service, FormBuilder $form_builder, ConfigFactory $configFactory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->heartbeatTypeServices = $heartbeat_heartbeattype;
    $this->heartbeatStreamServices = $heartbeatstream;
    $this->heartbeatService = $heartbeat;
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
    $this->flagService = $flag_service;
    $this->formBuilder = $form_builder;
    $this->configFactory = $configFactory;
    $this->timestamp = time();
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
      $container->get('flag'),
      $container->get('form_builder'),
      $container->get('config.factory')
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
    if ($feed !== null && $this->heartbeatStreamServices) {
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
      $timeago = null;
      $diff = $this->timestamp - $heartbeat->getCreatedTime();
      switch (true) {
        case ($diff < 86400):
          $timeago = $this->dateFormatter->formatInterval(REQUEST_TIME - $heartbeat->getCreatedTime()) . ' ago';
          break;
        case ($diff >= 86400 && $diff < 172800):
          $timeago = 'Yesterday at ' . $this->dateFormatter->format($heartbeat->getCreatedTime(), 'heartbeat_time');
          break;
        case ($diff >= 172800):
          $timeago = $this->dateFormatter->format($heartbeat->getCreatedTime(), 'heartbeat_medium');
          break;
      }

      $user = $heartbeat->getOwner();
      $userView = user_view($user, 'compact');
      $userPic = $user->get('user_picture')->getValue();

      if (!empty($userPic)) {
        $profilePic = $user->get('user_picture')->getValue()[0]['target_id'];
      }

      if (NULL === $profilePic) {
        $profilePic = 86;
      }

      $pic = File::load($profilePic);

      if ($pic !== NULL) {
        $style = $this->entityTypeManager->getStorage('image_style')
          ->load('thumbnail');
        $rendered = $style->buildUrl($pic->getFileUri());
      }

      $cids = \Drupal::entityQuery('comment')
        ->condition('entity_id', $heartbeat->id())
        ->condition('entity_type', 'heartbeat')
        ->sort('cid', 'ASC')
        ->execute();

      $comments = [];

      foreach($cids as $cid) {

        $url = Url::fromRoute('heartbeat.sub_comment_request', array('cid' => $cid));
        $commentLink = Link::fromTextAndUrl(t('Reply'), $url);
        $commentLink = $commentLink->toRenderable();
        $commentLink['#attributes'] = array('class' => array('button', 'button-action', 'use-ajax'));

        $comment = Comment::load($cid);

        $commentOwner = user_view($comment->getOwner(), 'comment');

        $subCids = \Drupal::entityQuery('comment')
          ->condition('entity_id', $cid)
          ->condition('entity_type', 'comment')
          ->sort('cid', 'ASC')
          ->execute();

        $subComments = [];
        if (count($subCids) > 0) {
          foreach ($subCids as $subCid) {
            $subComment = Comment::load($subCid);

            $subDiff = $this->timestamp - $subComment->getCreatedTime();

            switch (true) {
              case ($subDiff < 86400):
                $timeago = $this->dateFormatter->formatInterval(REQUEST_TIME - $subComment->getCreatedTime()) . ' ago';
                break;
              case ($subDiff >= 86400 && $subDiff < 172800):
                $timeago = 'Yesterday at ' . $this->dateFormatter->format($subComment->getCreatedTime(), 'heartbeat_time');
                break;
              case ($subDiff >= 172800):
                $timeago = $this->dateFormatter->format($subComment->getCreatedTime(), 'heartbeat_medium');
                break;
            }

            $subCommentOwner = user_view($subComment->getOwner(), 'comment');
            $subCommentTime = $this->timestamp - $subComment->getCreatedTime() < 172800 ? $this->dateFormatter->formatInterval(REQUEST_TIME - $subComment->getCreatedTime()) . ' ago': $this->dateFormatter->format($subComment->getCreatedTime(), 'heartbeat_medium');
            $subComments[] = [
              'id' => $subCid,
              'body' => $subComment->get('comment_body')->value,
              'username' => $subComment->getAuthorName(),
              'owner' => $subCommentOwner,
              'timeAgo' => $subCommentTime,
              'commentLike' => Heartbeat::flagAjaxMarkup('heartbeat_like_comment', $subComment, $this->flagService)
            ];

          }
        }

        $commentTimeDiff = $this->timestamp - $comment->getCreatedTime();

        switch (true) {
          case ($commentTimeDiff < 86400):
            $cTimeago = $this->dateFormatter->formatInterval(REQUEST_TIME - $comment->getCreatedTime()) . ' ago';
            break;
          case ($commentTimeDiff >= 86400 && $commentTimeDiff < 172800):
            $cTimeago = 'Yesterday at ' . $this->dateFormatter->format($comment->getCreatedTime(), 'heartbeat_time');
            break;
          case ($commentTimeDiff >= 172800):
            $cTimeago = $this->dateFormatter->format($comment->getCreatedTime(), 'heartbeat_medium');
            break;
        }

        $comments[] = [
          'id' => $cid,
          'body' => $comment->get('comment_body')->value,
          'username' => $comment->getAuthorName(),
          'owner' => $commentOwner,
          'timeAgo' => $cTimeago,
          'commentLike' => Heartbeat::flagAjaxMarkup('heartbeat_like_comment', $comment, $this->flagService),
          'reply' => $commentLink,
          'subComments' => $subComments
        ];

      }

      $form = \Drupal::service('form_builder')->getForm('\Drupal\heartbeat\Form\HeartbeatCommentForm', $heartbeat);
      $commentCount = count($comments);
      $messages[] = array('heartbeat' => $heartbeat->getMessage()->getValue()[0]['value'],
        'userPicture' => $rendered,
        'userId' => $user->id(),
        'timeAgo' => $timeago,
        'id' => $heartbeat->id(),
        'userName' => $user->getAccountName(),
        'user' => $userView,
        'commentForm' => $form,
        'comments' => array_reverse($comments),
        'commentCount' => $commentCount > 0 ? $commentCount : '',
        'likeFlag' => Heartbeat::flagAjaxMarkup('heartbeat_like', $heartbeat, $this->flagService),
        'unlikeFlag' => Heartbeat::flagAjaxMarkup('jihad_flag', $heartbeat, $this->flagService)
        );
    }
}


/******************************
 * *****FOR COMMENT FEED*******
 * *****ON EACH HEARTBEAT******
 * ****************************/

