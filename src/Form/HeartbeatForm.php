<?php

namespace Drupal\heartbeat8\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Heartbeat edit forms.
 *
 * @ingroup heartbeat8
 */
class HeartbeatForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\heartbeat8\Entity\Heartbeat */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    if (!$this->entity->isNew()) {
      $form['new_revision'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      );

      $form['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#maxlength' => 255,
        '#default_value' => $entity->label(),
        '#description' => $this->t("Label for the Heartbeat."),
        '#required' => TRUE,
      ];
    }



    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime(REQUEST_TIME);
      $entity->setRevisionUserId(\Drupal::currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Heartbeat.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Heartbeat.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.heartbeat.canonical', ['heartbeat' => $entity->id()]);
  }

}
