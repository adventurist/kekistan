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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
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
