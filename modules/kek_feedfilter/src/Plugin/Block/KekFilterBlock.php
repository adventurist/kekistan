<?php

namespace Drupal\kek_feedfilter\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Driver\pgsql\Connection;

/**
 * Provides a 'KekFilterBlock' block.
 *
 * @Block(
 *  id = "kek_filter_block",
 *  admin_label = @Translation("Kek filter block"),
 * )
 */
class KekFilterBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Database\Driver\pgsql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\pgsql\Connection
   */
  protected $database;

  /**
   * Constructs a new KekFilterBlock object.
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
        Connection $database
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $filterConfig= \Drupal::service('config.factory')->getEditable('kek_feed_filter_config.settings');
    $hashtags = [];

    $query = '
      SELECT count(ft.field_tags_target_id), t.name, ft.field_tags_target_id
      FROM node__field_tags ft
      INNER JOIN taxonomy_term_field_data t
      ON t.tid = ft.field_tags_target_id
      INNER JOIN node_field_data n
      ON n.nid = ft.entity_id WHERE n.created::int > (extract(epoch from now()) - 2419200)::int
      GROUP BY ft.field_tags_target_id, t.name, ft.field_tags_target_id
      ORDER BY count(ft.field_tags_target_id) DESC limit 100';

    $result = $this->database->query($query);
    $tags = [];
    $i = 0;
    if ($executed = $result->fetchAll()) {

      foreach ($executed as $tag) {
        if ($i < 10) {
          $tags[] = ['name' => $tag->name, 'count' => $tag->count, 'tid' => $tag->field_tags_target_id];
        }
        $tagData = new \stdClass();
        $tagData->name = $tag->name;
        $tagData->tid = $tag->field_tags_target_id;
        $tagData->count = $tag->count;

        $hashtags[] = $tagData;

        $i++;
      }

      return [
        '#theme' => 'kekfilter',
        '#tags' => $tags,
        '#attached' => array(
          'library' => 'kek_feedfilter/feedfilter',
          'drupalSettings' => [
            'hashtags' => $hashtags,
          ])
      ];
    }
    return null;
  }
}
