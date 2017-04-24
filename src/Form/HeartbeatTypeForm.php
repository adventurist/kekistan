<?php

namespace Drupal\heartbeat8\Form;

use Drupal\heartbeat8;
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

    $form_state->setCached(FALSE);

    $heartbeat_type = $this->entity;

    $form['#tree'] = TRUE;

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $heartbeat_type->label(),
      '#description' => $this->t("Label for the Heartbeat Type."),
      '#required' => TRUE,
    );


    $form['message_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('messageId'),
      '#maxlength' => 255,
      '#default_value' => "New Message ID",
      '#description' => $this->t("Message ID for the Heartbeat Type."),
      '#required' => TRUE,
    );


    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('description'),
      '#maxlength' => 255,
      '#default_value' => "Description",
      '#description' => $this->t("Description of the Heartbeat Type"),
      '#required' => TRUE,
    );


    $form['message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('message'),
      '#maxlength' => 255,
      '#default_value' => "Message",
      '#description' => $this->t("The structure for messages of this type. Use !exclamation marks before fields and entities"),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::rebuildMessageArguments',
        'event' => 'change',
        'progress' => array(
          'type' => 'throbber',
          'message' => t('Rebuilding arguments'),
        ),
      ]
    );


    $form['message_concat'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Message structure in concatenated form'),
      '#maxlength' => 255,
      '#default_value' => "Message",
      '#description' => $this->t("The structure for messages of this type. Use !exclamation marks before fields and entities"),
      '#required' => FALSE,
    );


    $form['perms'] = array(
      '#type' => 'select',
      '#title' => $this->t('Permissions'),
      '#default_value' => 0,
      '#description' => $this->t("Default permissions to view Heartbeats of this type"),
      '#options' => array(
        0 => heartbeat8\HEARTBEAT_NONE,
        1 => heartbeat8\HEARTBEAT_PRIVATE,
        2 => heartbeat8\HEARTBEAT_PUBLIC_TO_ADDRESSEE,
        3 => heartbeat8\HEARTBEAT_PUBLIC_TO_CONNECTED,
        4 => heartbeat8\HEARTBEAT_PUBLIC_TO_ALL,

      ),
      '#required' => TRUE,
    );


    $form['group_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Group Type'),
      '#default_value' => 0,
      '#description' => $this->t("Type of group associated with Heartbeats of this type"),
      '#options' => array(
        0 => heartbeat8\HEARTBEAT_GROUP_NONE,
        1 => heartbeat8\HEARTBEAT_GROUP_SINGLE,
        2 => heartbeat8\HEARTBEAT_GROUP_SUMMARY,
      ),
      '#required' => TRUE,
    );

    $form['variables'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Variables to map'),
      '#prefix' => '<div id="names-fieldset-wrapper">',
      '#suffix' => '</div>',
    );

    if ($form_state->get('data_hidden') == NULL) $form_state->set('data_hidden', array());
    $messageArguments = $form_state->get('data_hidden');

    $argNum = count($messageArguments);

    for ($i = 0; $i < $argNum; $i++) {

      $form['variables']['variable'][$i] = array(
        '#type' => 'textfield',
        '#title' => t($messageArguments[$i]),
        '#description' => t('Define message argument'),
      );

    }

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $heartbeat_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\heartbeat8\Entity\HeartbeatType::load',
      ],
      '#disabled' => !$heartbeat_type->isNew(),
    ];

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



  /**
   * Custom form validation to rebuild
   * Form field for mapping Message Arguments
   */

  public function rebuildMessageArguments(array &$form, FormStateInterface $form_state) {

    \Drupal::logger('HeartbeatTypeFormDEBUG')->notice('Ajax callback successfully called');

    $messageArgString = $form_state->getValue('message');
    $messageArguments = explode('!', $messageArgString);

    $argsArray = array();

    foreach ($messageArguments as $argument) {

      if (strlen($argument) > 0) {

        $cleanArgument = substr($argument, 0, strpos($argument, ' '));
        $argsArray[] = $cleanArgument;

      }
    }

    $form_state->set('data_hidden', $argsArray);
    $form_state->setRebuild();

  }
}
