<?php

namespace Drupal\heartbeat\Controller;

use Drupal\block\BlockViewBuilder;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\Element\Ajax;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\heartbeat\Ajax\SubCommentCommand;
use Drupal\heartbeat\Entity\HeartbeatInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HeartbeatController.
 *
 *  Returns responses for Heartbeat routes.
 *
 * @package Drupal\heartbeat\Controller
 */
class HeartbeatController extends ControllerBase {

  private $renderer;
  private $blockManager;

  /**
   * @param ContainerInterface $container
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * HeartbeatController constructor.
   * @param Renderer $renderer
   * @param BlockManager $block_manager
   */

  public function __construct(Renderer $renderer, BlockManager $block_manager) {
    $this->renderer = $renderer;
    $this->blockManager = $block_manager;
  }
  /**
   * Displays a Heartbeat  revision.
   *
   * @param int $heartbeat_revision
   *   The Heartbeat  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($heartbeat_revision) {
    $heartbeat = $this->entityManager()->getStorage('heartbeat')->loadRevision($heartbeat_revision);
    $view_builder = $this->entityManager()->getViewBuilder('heartbeat');

    return $view_builder->view($heartbeat);
  }

  /**
   * Page title callback for a Heartbeat  revision.
   *
   * @param int $heartbeat_revision
   *   The Heartbeat  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($heartbeat_revision) {
    $heartbeat = $this->entityManager()->getStorage('heartbeat')->loadRevision($heartbeat_revision);
    return $this->t('Revision of %title from %date', array('%title' => $heartbeat->label(), '%date' => format_date($heartbeat->getRevisionCreationTime())));
  }

  /**
   * Generates an overview table of older revisions of a Heartbeat .
   *
   * @param \Drupal\heartbeat\Entity\HeartbeatInterface $heartbeat
   *   A Heartbeat  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(HeartbeatInterface $heartbeat) {
    $account = $this->currentUser();
    $langcode = $heartbeat->language()->getId();
    $langname = $heartbeat->language()->getName();
    $languages = $heartbeat->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $heartbeat_storage = $this->entityManager()->getStorage('heartbeat');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $heartbeat->label()]) : $this->t('Revisions for %title', ['%title' => $heartbeat->label()]);
    $header = array($this->t('Revision'), $this->t('Operations'));

    $revert_permission = (($account->hasPermission("revert all heartbeat revisions") || $account->hasPermission('administer heartbeat entities')));
    $delete_permission = (($account->hasPermission("delete all heartbeat revisions") || $account->hasPermission('administer heartbeat entities')));

    $rows = array();

    $vids = $heartbeat_storage->revisionIds($heartbeat);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\heartbeat\HeartbeatInterface $revision */
      $revision = $heartbeat_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->revision_timestamp->value, 'short');
        if ($vid != $heartbeat->getRevisionId()) {
          $link = $this->l($date, new Url('entity.heartbeat.revision', ['heartbeat' => $heartbeat->id(), 'heartbeat_revision' => $vid]));
        }
        else {
          $link = $heartbeat->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->revision_log_message->value, '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.heartbeat.translation_revert', ['heartbeat' => $heartbeat->id(), 'heartbeat_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.heartbeat.revision_revert', ['heartbeat' => $heartbeat->id(), 'heartbeat_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.heartbeat.revision_delete', ['heartbeat' => $heartbeat->id(), 'heartbeat_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['heartbeat_revisions_table'] = array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    );

    return $build;
  }

  public function renderFeed($arg) {
    $myConfig = \Drupal::service('config.factory')->getEditable('heartbeat_feed.settings');
    $myConfig->set('message', $arg)->save();
    \Drupal::logger('HeartbeatController')->debug('My argument is %arg', ['%arg' => $arg]);

    return BlockViewBuilder::lazyBuilder('heartbeatblock', 'full');
  }


  public function updateFeed($hid) {
    $myConfig = \Drupal::service('config.factory')->getEditable('heartbeat_more.settings');
    $myConfig->set('hid', $hid)->save();
    $block = $this->blockManager->createInstance('heartbeat_more_block')->build();
    return [
      '#type' => 'markup',
      '#markup' => $this->renderer->render($block)
    ];
  }

  public function filterFeed($tid) {
    $myConfig = \Drupal::service('config.factory')->getEditable('heartbeat_hashtag.settings');
    $myConfig->set('tid', $tid)->save();
    $block = $this->blockManager->createInstance('heartbeat_hash_block')->build();

    return [
      '#type' => 'markup',
      '#markup' => $this->renderer->render($block)
      ];
  }


  public function userFilterFeed($tid) {
    $myConfig = \Drupal::service('config.factory')->getEditable('heartbeat_username.settings');
    $myConfig->set('tid', $tid)->save();
    $block = $this->blockManager->createInstance('heartbeat_username_block')->build();

    return [
      '#type' => 'markup',
      '#markup' => $this->renderer->render($block)
    ];
  }


  public function commentConfigUpdate($entity_id) {
    $commentConfig = \Drupal::configFactory()->getEditable('heartbeat_comment.settings');
    $commentConfig->set('entity_id', $entity_id)->save();

    return [
      '#type' => 'markup',
      '#markup' => 'Success',
    ];
  }

  public function subCommentRequest($cid) {
    $subCommentConfig = \Drupal::configFactory()->getEditable('heartbeat_comment.settings');
    $subCommentConfig->set('cid', $cid)->save();

    $response = new AjaxResponse();
    $response->addCommand(new AppendCommand('#heartbeat-comment-' . $cid,
      BlockViewBuilder::lazyBuilder('heartbeatsubcommentblock', 'teaser')));
    $response->addCommand(new SubCommentCommand($cid));

    return $response;

  }

  public function subComment() {
    return BlockViewBuilder::lazyBuilder('heartbeatsubcommentblock', 'teaser');
  }

  public function friendInteract($uid) {
    $myConfig = \Drupal::service('config.factory')->getEditable('heartbeat_friend_interact.settings');
    $myConfig->set('uid', $uid)->save();
//    $block = BlockViewBuilder::lazyBuilder('friendinteractblock', 'full');
    $block = \Drupal::service('plugin.manager.block');
    $block = $block->createInstance('friend_interact_block')->build();
    $blockMarkup = \Drupal::service('renderer')->render($block);

    return ['#type' => 'markup', '#markup' => $blockMarkup];
  }
}
