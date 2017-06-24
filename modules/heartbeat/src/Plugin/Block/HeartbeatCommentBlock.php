<?php

namespace Drupal\heartbeat\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\heartbeat\Form\HeartbeatCommentForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilder;

/**
 * Provides a 'HeartbeatCommentBlock' block.
 *
 * @Block(
 *  id = "heartbeat_comment_block",
 *  admin_label = @Translation("Heartbeat comment block"),
 * )
 */
class HeartbeatCommentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Form\FormBuilder definition.
   *
   * @var Drupal\Core\Form\FormBuilder
   */
  protected $form_builder;
  protected $entityId;

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
        FormBuilder $form_builder
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->form_builder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }



  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = $this->form_builder->getForm('\Drupal\heartbeat\Form\HeartbeatCommentForm');
    if ($form instanceof HeartbeatCommentForm) {
      $form->setEntityId($this->entityId);
    }
    return $form;
  }

  public function setEntityId($id) {
    $this->entityId = $id;
  }

}
