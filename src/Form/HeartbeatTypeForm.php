<?php

namespace Drupal\heartbeat8\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class HeartbeatTypeForm.
 *
 * @package Drupal\heartbeat8\Form
 */
class HeartbeatTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $heartbeat_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $heartbeat_type->label(),
      '#description' => $this->t("Label for the Heartbeat type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $heartbeat_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\heartbeat8\Entity\HeartbeatType::load',
      ],
      '#disabled' => !$heartbeat_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $heartbeat_type = $this->entity;
    $status = $heartbeat_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Heartbeat type.', [
          '%label' => $heartbeat_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Heartbeat type.', [
          '%label' => $heartbeat_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($heartbeat_type->toUrl('collection'));
  }

}
