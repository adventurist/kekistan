<?php

namespace Drupal\heartbeat8\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Heartbeat stream edit forms.
 *
 * @ingroup heartbeat8
 */
class HeartbeatStreamForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\heartbeat8\Entity\HeartbeatStream */
    $form = parent::buildForm($form, $form_state);

    if (!$this->entity->isNew()) {
      $form['new_revision'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      );
    }
//    $query = Database::getConnection()->select('heartbeat_type')
    $form['types'] = array(
      '#type' => 'checkboxes',
      '#options' => array(

      ),
      '#title' => $this->t('Please select all the Heartbeat Types you wish to include in this stream'),
    );

    $entity = $this->entity;

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
        drupal_set_message($this->t('Created the %label Heartbeat stream.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Heartbeat stream.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.heartbeat_stream.canonical', ['heartbeat_stream' => $entity->id()]);
  }

}
