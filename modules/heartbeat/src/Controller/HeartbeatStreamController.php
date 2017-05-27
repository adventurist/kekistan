<?php

namespace Drupal\heartbeat\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\heartbeat\HeartbeatService;
use Drupal\heartbeat\HeartbeatStreamServices;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\heartbeat\Entity\HeartbeatStreamInterface;

/**
 * Class HeartbeatStreamController.
 *
 *  Returns responses for Heartbeat stream routes.
 *
 * @package Drupal\heartbeat\Controller
 */
class HeartbeatStreamController extends ControllerBase {

  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;
  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * \Drupal\heartbeat\HeartbeatStreamServices definition.
   *
   * @var \Drupal\heartbeat\HeartbeatStreamServices
   */
  protected $heartbeatStreamService;

  /**
   * \Drupal\heartbeat\HeartbeatService definition.
   *
   * @var \Drupal\heartbeat\HeartbeatService
   */
  protected $heartbeatService;

  /**
   * {@inheritdoc}
   */
  public function __construct(QueryFactory $entity_query, EntityTypeManager $entity_type_manager, HeartbeatStreamServices $heartbeatStreamService, HeartbeatService $heartbeatService) {
    $this->entityQuery = $entity_query;
    $this->entityTypeManager = $entity_type_manager;
    $this->heartbeatStreamService = $heartbeatStreamService;
    $this->heartbeatService = $heartbeatService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('entity_type.manager'),
      $container->get('heartbeatstream'),
      $container->get('heartbeat')
    );
  }

  /**
   * Getroutes.
   *
   * @return string
   *   Return Hello string.
   */
  public function createRoute($heartbeatStreamId) {

    $messages = array();
    $heartbeatTypes = array();
    $types = $this->heartbeatStreamService->getTypesById($heartbeatStreamId);
    foreach ($types as $type) {
      if ($type != null && strlen($type->getValue()['target_id']) > 1) {
        $heartbeatTypes[] = $type->getValue()['target_id'];
      }
    }
    $heartbeats = $this->heartbeatService->loadByTypes($heartbeatTypes);

    foreach($heartbeats as $heartbeat) {
      $messages[] = $heartbeat->getMessage()->getValue()[0]['value'];
    }
    return [
      '#theme' => 'heartbeat_stream',
      '#messages' => $messages,
      '#attached' => array('library' => 'heartbeat/heartbeat')
    ];

  }

  /**
   * Displays a Heartbeat stream  revision.
   *
   * @param int $heartbeat_stream_revision
   *   The Heartbeat stream  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($heartbeat_stream_revision) {
    $heartbeat_stream = $this->entityManager()->getStorage('heartbeat_stream')->loadRevision($heartbeat_stream_revision);
    $view_builder = $this->entityManager()->getViewBuilder('heartbeat_stream');

    return $view_builder->view($heartbeat_stream);
  }

  /**
   * Page title callback for a Heartbeat stream  revision.
   *
   * @param int $heartbeat_stream_revision
   *   The Heartbeat stream  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($heartbeat_stream_revision) {
    $heartbeat_stream = $this->entityManager()->getStorage('heartbeat_stream')->loadRevision($heartbeat_stream_revision);
    return $this->t('Revision of %title from %date', array('%title' => $heartbeat_stream->label(), '%date' => format_date($heartbeat_stream->getRevisionCreationTime())));
  }

  /**
   * Generates an overview table of older revisions of a Heartbeat stream .
   *
   * @param \Drupal\heartbeat\Entity\HeartbeatStreamInterface $heartbeat_stream
   *   A Heartbeat stream  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(HeartbeatStreamInterface $heartbeat_stream) {
    $account = $this->currentUser();
    $langcode = $heartbeat_stream->language()->getId();
    $langname = $heartbeat_stream->language()->getName();
    $languages = $heartbeat_stream->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $heartbeat_stream_storage = $this->entityManager()->getStorage('heartbeat_stream');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $heartbeat_stream->label()]) : $this->t('Revisions for %title', ['%title' => $heartbeat_stream->label()]);
    $header = array($this->t('Revision'), $this->t('Operations'));

    $revert_permission = (($account->hasPermission("revert all heartbeat stream revisions") || $account->hasPermission('administer heartbeat stream entities')));
    $delete_permission = (($account->hasPermission("delete all heartbeat stream revisions") || $account->hasPermission('administer heartbeat stream entities')));

    $rows = array();

    $vids = $heartbeat_stream_storage->revisionIds($heartbeat_stream);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\heartbeat\HeartbeatStreamInterface $revision */
      $revision = $heartbeat_stream_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->revision_timestamp->value, 'short');
        if ($vid != $heartbeat_stream->getRevisionId()) {
          $link = $this->l($date, new Url('entity.heartbeat_stream.revision', ['heartbeat_stream' => $heartbeat_stream->id(), 'heartbeat_stream_revision' => $vid]));
        }
        else {
          $link = $heartbeat_stream->link($date);
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
              'url' => Url::fromRoute('entity.heartbeat_stream.revision_revert', ['heartbeat_stream' => $heartbeat_stream->id(), 'heartbeat_stream_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.heartbeat_stream.revision_delete', ['heartbeat_stream' => $heartbeat_stream->id(), 'heartbeat_stream_revision' => $vid]),
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

    $build['heartbeat_stream_revisions_table'] = array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    );

    return $build;
  }

}
