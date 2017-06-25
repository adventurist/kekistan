<?php

namespace Drupal\heartbeat\Controller;

use Drupal\block\BlockViewBuilder;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\heartbeat\Entity\HeartbeatInterface;

/**
 * Class HeartbeatController.
 *
 *  Returns responses for Heartbeat routes.
 *
 * @package Drupal\heartbeat\Controller
 */
class HeartbeatController extends ControllerBase implements ContainerInjectionInterface {

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

    return BlockViewBuilder::lazyBuilder('heartbeatmoreblock', 'full');
  }

  public function filterFeed($tid) {
    $myConfig = \Drupal::service('config.factory')->getEditable('heartbeat_hashtag.settings');
    $myConfig->set('tid', $tid)->save();

    return BlockViewBuilder::lazyBuilder('heartbeathashblock', 'teaser');
  }

  public function commentConfigUpdate($entity_id) {
    $commentConfig = \Drupal::configFactory()->getEditable('heartbeat_comment.settings');
    $commentConfig->set('entity_id', $entity_id)->save();

    return [
      '#type' => 'markup',
      '#markup' => 'Success',
    ];
  }

}
