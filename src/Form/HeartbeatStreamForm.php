<?php

namespace Drupal\heartbeat8\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class HeartbeatStreamForm.
 *
 * @package Drupal\heartbeat8\Form
 */
class HeartbeatStreamForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $heartbeat_stream = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $heartbeat_stream->label(),
      '#description' => $this->t("Label for the Heartbeat stream."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $heartbeat_stream->id(),
      '#machine_name' => [
        'exists' => '\Drupal\heartbeat8\Entity\HeartbeatStream::load',
      ],
      '#disabled' => !$heartbeat_stream->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $heartbeat_stream = $this->entity;
    $status = $heartbeat_stream->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Heartbeat stream.', [
          '%label' => $heartbeat_stream->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Heartbeat stream.', [
          '%label' => $heartbeat_stream->label(),
        ]));
    }
    $form_state->setRedirectUrl($heartbeat_stream->toUrl('collection'));
  }

}
