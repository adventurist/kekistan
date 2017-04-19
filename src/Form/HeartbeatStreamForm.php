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
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $heartbeat_stream->label(),
      '#description' => $this->t("Label for the Heartbeat Stream."),
      '#required' => TRUE,
    );


    $form['message_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('messageId'),
      '#maxlength' => 255,
      '#default_value' => "New Message ID",
      '#description' => $this->t("Message ID for the Heartbeat Stream."),
      '#required' => TRUE,
    );


    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('description'),
      '#maxlength' => 255,
      '#default_value' => "Description",
      '#description' => $this->t("Description of the Heartbeat Stream"),
      '#required' => TRUE,
    );

    $form['message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('message'),
      '#maxlength' => 255,
      '#default_value' => "Message",
      '#description' => $this->t("The structure for messages of this type. Use !exclamation marks before fields and entities"),
      '#required' => TRUE,
    );




    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $heartbeat_stream->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\heartbeat8\Entity\HeartbeatStream::load',
      ),
      '#disabled' => !$heartbeat_stream->isNew(),
    );

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
        drupal_set_message($this->t('Created the %label Heartbeat Stream.', [
          '%label' => $heartbeat_stream->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Heartbeat Stream.', [
          '%label' => $heartbeat_stream->label(),
        ]));
    }
    $form_state->setRedirectUrl($heartbeat_stream->urlInfo('collection'));
  }

}
