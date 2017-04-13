<?php

namespace Drupal\heartbeat8\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class HeartbeatStreamEntityForm.
 *
 * @package Drupal\heartbeat8\Form
 */
class HeartbeatStreamEntityForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $heartbeat_stream_entity = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $heartbeat_stream_entity->label(),
      '#description' => $this->t("Label for the Heartbeat stream entity."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $heartbeat_stream_entity->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\heartbeat8\Entity\HeartbeatStreamEntity::load',
      ),
      '#disabled' => !$heartbeat_stream_entity->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $heartbeat_stream_entity = $this->entity;
    $status = $heartbeat_stream_entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Heartbeat stream entity.', [
          '%label' => $heartbeat_stream_entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Heartbeat stream entity.', [
          '%label' => $heartbeat_stream_entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($heartbeat_stream_entity->urlInfo('collection'));
  }

}
