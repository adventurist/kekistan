<?php

namespace Drupal\heartbeat\Plugin\Block;

use Drupal\Core\Block\BlockBase;
//use Drupal\Core\Asset\AssetResolver;
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
	HeartbeatService $heartbeat
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->heartbeatTypeServices = $heartbeat_heartbeattype;
    $this->heartbeatStreamServices = $heartbeatstream;
    $this->heartbeatService = $heartbeat;
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
      $container->get('heartbeat')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build() {

    $myConfig = \Drupal::service('config.factory')->getEditable('heartbeat_feed.settings');

    $feed = $myConfig->get('message');

    $messages = array();

    $query = Database::getConnection()->select('heartbeat_friendship', 'hf')
      ->fields('hf',['uid_target'])
      ->condition('hf.uid', \Drupal::currentUser()->id())->execute();

    if ($result = $query->fetchAll()) {
      $uids = array();
      foreach ($result as $uid) {
        $uids[] = $uid->uid_target;
      }
    }
      if ($feed !== null) {
        if ($uids !== null && count($uids) > 0) {
          foreach ($this->heartbeatStreamServices->createStreamForUidsByType($uids, $feed) as $heartbeat) {
            $messages[] = $heartbeat->getMessage()->getValue()[0]['value'];
          }
        } else {
          foreach ($this->heartbeatStreamServices->createStreamByType($feed) as $heartbeat) {
            $messages[] = $heartbeat->getMessage()->getValue()[0]['value'];
          }
        }
      } else {
//        foreach ($this->heartbeatStreamServices->createStreamForUids($uids) as $heartbeat) {
        foreach ($this->heartbeatStreamServices->loadAllStreams() as $heartbeat) {
          $messages[] = $heartbeat->getMessage()->getValue()[0]['value'];
        }
      }

      return [
        '#theme' => 'heartbeat_stream',
        '#messages' => $messages,
        '#attached' => array(
          'library' => 'heartbeat/heartbeat',
          'drupalSettings' => ['activeFeed' => 'jigga']
        ),
        '#cache' => array('max-age' => 0)
      ];

    }
}
