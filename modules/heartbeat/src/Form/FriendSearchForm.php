<?php

namespace Drupal\heartbeat\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigManager;

/**
 * Class FriendSearchForm.
 */
class FriendSearchForm extends FormBase {

  /**
   * Drupal\Core\Config\ConfigManager definition.
   *
   * @var \Drupal\Core\Config\ConfigManager
   */
  private $configManager;

  /**
   * Constructs a new FriendSearchForm object.
   */
  public function __construct(
    ConfigManager $config_manager
  ) {
    $this->configManager = $config_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.manager')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'friend_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['search'] = [
      '#type' => 'entity_autocomplete',
      '#entity_type' => 'user',
      '#target_type' => 'user',
      '#autocomplete_route_name' => 'entity-autocomplete/user/',
      '#title' => $this->t('Friends'),
      '#description' => $this->t('Search for friends by name'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#ajax' => [
        'callback' => '::friendSearchAjaxSubmit',
        'progress' => array(
          'type' => 'throbber',
          'message' => t('Finding friends'),
        ),
      ]
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return null;
  }


  public function friendSearchAjaxSubmit(array &$form, FormStateInterface $form_state) {
    $ajax_response = new AjaxResponse();
    $ajax_response->addCommand(new InvokeCommand(NULL, 'updateFriendView', ['some Var']));
    return $ajax_response;
  }
}
