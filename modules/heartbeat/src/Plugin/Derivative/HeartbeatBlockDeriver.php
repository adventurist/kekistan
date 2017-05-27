<?php

namespace Drupal\heartbeat\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\heartbeat\HeartbeatTypeServices;
use Drupal\heartbeat\HeartbeatStreamServices;
use Drupal\heartbeat\HeartbeatService;

/**
 * Provides a block plugin definitions for Heartbeat
 *
 */
class HeartbeatBlockDeriver extends DeriverBase implements ContainerDeriverInterface {

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
    $plugin_id,
    HeartbeatTypeServices $heartbeat_heartbeattype,
    HeartbeatStreamServices $heartbeatstream,
    HeartbeatService $heartbeat
  ) {
    parent::__construct($plugin_id);
    $this->heartbeatTypeService = $heartbeat_heartbeattype;
    $this->heartbeatStreamServices = $heartbeatstream;
    $this->heartbeatService = $heartbeat;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $plugin_id) {
    return new static(


      $plugin_id,

      $container->get('heartbeat.heartbeattype'),
      $container->get('heartbeatstream'),
      $container->get('heartbeat')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['heartbeat_block']['#markup'] = 'Implement HeartbeatBlock.';

    return $build;
  }

  public function getDerivativeDefinitions($base_plugin_definition) {
    $def2 = parent::getDerivativeDefinitions($base_plugin_definition);
    return $base_plugin_definition;
  }

}

