<?php

namespace Drupal\heartbeat\Form;

use Drupal\Core\Form\FormAjaxException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Ajax;
use Drupal\heartbeat\Ajax\SelectFeedCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\heartbeat\HeartbeatService;
use Drupal\heartbeat\HeartbeatTypeServices;
use Drupal\heartbeat\HeartbeatStreamServices;
use Drupal\Core\Ajax\AjaxResponse;


/**
 * Class HeartbeatFeedForm.
 *
 * @package Drupal\heartbeat\Form
 */
class HeartbeatFeedForm extends FormBase {

  /**
   * Drupal\heartbeat\HeartbeatService definition.
   *
   * @var \Drupal\heartbeat\HeartbeatService
   */
  protected $heartbeatService;
  /**
   * Drupal\heartbeat\HeartbeatTypeServices definition.
   *
   * @var \Drupal\heartbeat\HeartbeatTypeServices
   */
  protected $typeService;
  /**
   * Drupal\heartbeat\HeartbeatStreamServices definition.
   *
   * @var \Drupal\heartbeat\HeartbeatStreamServices
   */
  protected $streamService;

    /**
     * The currently selected stream
     * @var
     */
  private $stream;

  private $streams;


  public function __construct(
    HeartbeatService $heartbeat,
    HeartbeatTypeServices $heartbeat_heartbeattype,
    HeartbeatStreamServices $heartbeatstream
  ) {
    $this->heartbeatService = $heartbeat;
    $this->typeService = $heartbeat_heartbeattype;
    $this->streamService = $heartbeatstream;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('heartbeat'),
      $container->get('heartbeat.heartbeattype'),
      $container->get('heartbeatstream')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'heartbeat_feed_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

      if ($this->stream === null) {
          $this->stream = $form_state->getValue('feedtabs');
      }
      if ($this->streams === null) {
          foreach ($this->streamService->getAllStreams() as $heartbeatStream) {
              $this->streams[$heartbeatStream->getName()] = t($heartbeatStream->getName());
          }
      }

    $form['feedtabs'] = [
      '#type' => 'radios',
      '#title' => $this->t('Choose a feed'),
//      '#description' => $this->t('User selectable feeds'),
      '#options' => $this->streams,
      '#ajax' => [
        'callback' => '::updateFeed',
//        'event' => 'onclick',
        'progress' => array(
        'type' => 'none',
//        'message' => t('Fetching feed'),
        ),
      ]
    ];

//    $form['feedsearch'] = [
//      '#type' => 'search',
//    ];


    $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Search'),
    ];

    $form['#attached']['library'][] = 'heartbeat/heartbeat';

      return $form;
  }


    /**
     * @param array $form
     * @param FormStateInterface $form_state
     * @return AjaxResponse
     */
    public function updateFeed(array &$form, FormStateInterface $form_state) {

      $response = new AjaxResponse();
      $response->addCommand(new SelectFeedCommand($form_state->getValue('feedtabs')));

      return $response;

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
    $stophere = null;
    $stopthere = null;

    \Drupal::logger('HeartbeatFeedForm::submitForm')->debug('Jigga what is %arg', ['%arg' => $arg]);

    foreach ($form_state->getValues() as $key => $value) {
        drupal_set_message($key . ': ' . $value);
    }
  }

}
