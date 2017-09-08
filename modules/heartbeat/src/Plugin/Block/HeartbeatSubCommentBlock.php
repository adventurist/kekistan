<?php

namespace Drupal\heartbeat\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilder;

/**
 * Provides a 'HeartbeatSubCommentBlock' block.
 *
 * @Block(
 *  id = "heartbeat_sub_comment_block",
 *  admin_label = @Translation("Heartbeat sub comment block"),
 * )
 */
class HeartbeatSubCommentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Form\FormBuilder definition.
   *
   * @var Drupal\Core\form\FormBuilder
   */
  protected $form_builder;
  protected $entityId;
  protected $config;
  protected $entityTypeManager;

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
    FormBuilder $form_builder,
    EntityTypeManager $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->form_builder = $form_builder;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('entity_type.manager')
    );
  }



  /**
   * {@inheritdoc}
   */
  public function build() {

    if ($this->entityTypeManager !== null) {
      $comment = $this->entityTypeManager->getStorage('comment')->load(\Drupal::config('heartbeat_comment.settings')->get('cid'));

      return $this->form_builder->getForm('\Drupal\heartbeat\Form\HeartbeatSubCommentForm', $comment);
    }

    return null;
  }

  public function setEntityId($id) {
    $this->entityId = $id;
  }

}
