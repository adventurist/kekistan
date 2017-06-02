<?php

namespace Drupal\heartbeat\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Url;
use Drupal\heartbeat\Controller\HeartbeatAjaxException;
use Drupal\heartbeat\Ajax\UpdateFeedCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigManager;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Render\Element;

/**
 * Class HeartbeatUpdateFeedForm.
 *
 * @package Drupal\heartbeat\Form
 */
class HeartbeatUpdateFeedForm extends FormBase {

  private $triggered;
  /**
   * Drupal\Core\Config\ConfigManager definition.
   *
   * @var \Drupal\Core\Config\ConfigManager
   */
  protected $configManager;
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
    return 'heartbeat_update_feed_form';
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\heartbeat\Controller\HeartbeatAjaxException
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

//    $request = \Drupal::request();
//    if ($this->triggered) {
//      $this->triggered = false;
//      throw new \Drupal\heartbeat\Controller\HeartbeatAjaxException($this);
//    }

    $form['#attached']['libraries'][] = 'heartbeat/heartbeat';

    $form['timestamp'] = [
      '#type' => 'textfield',
      '#value' => t('Update Feeds'),
      '#ajax' => [
        'url' => Url::fromUri('internal:/heartbeat/form/heartbeat_update_feed'),
        'callback' => '::updateFeedCommand',
        'options' => array(
          'query' => array(
            'callback' => 'updateFeedCommand',
          ),
        ),
        'progress' => array(
          'type' => 'none,'
        )
      ]
    ];

    $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
        '#ajax' => [
          'callback' => '::updateFeedCommand',
          'url' => Url::fromUri('internal:/heartbeat/form/heartbeat_update_feed'),
          'progress' => array(
            'type' => 'none,'
          ),
          'options' => array(
            'query' => array(
              'callback' => 'updateFeedCommand',
            ),
          )
        ]
    ];

//    $this->prepareAjaxForm($form);

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
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
        drupal_set_message($key . ': ' . $value);
    }

    $form['#attached']['drupalSettings']['feedUpdate'] = 'timestamp';
//    $this->updateFeed($form, $form_state);
    $this->triggered = true;
    $this->buildForm($form, $form_state);
  }


  public function updateFeed(array &$form, FormStateInterface $form_state) {
    \Drupal::logger('HeartbeatUpdateFeedForm::updateFeed')->debug('update Feed is getting called');

    $feedUpdateConfig = new \stdClass();
    $feedUpdateConfig->feed = 'Node Activity';
    $feedUpdateConfig->update = true;
    $feedUpdateConfig->timestamp = 123456789;

    $ajaxResponse = new AjaxResponse;
    $updateCommand = new UpdateFeedCommand($feedUpdateConfig);

    $ajaxResponse->addCommand($updateCommand);

    return $ajaxResponse;

  }


  public function updateFeedCommand() {

    $feedUpdateConfig = new \stdClass();
    $feedUpdateConfig->feed = 'Node Activity';
    $feedUpdateConfig->update = true;
    $feedUpdateConfig->timestamp = 123456789;

    $updateCommand = new UpdateFeedCommand($feedUpdateConfig);

    return $updateCommand;
  }


  public function prepareAjaxForm(&$form) {
    if (!isset($form['#value'])) {
      $form['#value'] = isset($form['#default_value']) ? $form['#default_value'] : '';
    }
    if (isset($form['#type'])) {
      if ($form['#type'] == 'submit') {
        $form = RenderElement::preRenderAjaxForm($form);
      }
    }
    foreach(Element::children($form) as $key) {
      $element = &$form[$key];
      $this->prepareAjaxForm($element);
    }
  }

}
