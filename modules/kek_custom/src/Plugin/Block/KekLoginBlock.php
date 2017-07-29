<?php

namespace Drupal\kek_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'KekLoginBlock' block.
 *
 * @Block(
 *  id = "kek_login_block",
 *  admin_label = @Translation("Kek login block"),
 * )
 */
class KekLoginBlock extends BlockBase implements ContainerFactoryPluginInterface {


  /**
   * Drupal\Core\Form\FormBuilder definition.
   *
   * @var Drupal\Core\form\FormBuilder
   */
  protected $form_builder;

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
    return $this->form_builder->getForm('\Drupal\user\Form\UserLoginForm');
  }

}
