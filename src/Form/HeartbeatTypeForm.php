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
class HeartbeatTypeForm extends EntityForm
{

  /**
   * {@inheritdoc}
   */
  public function buildform(array $form, FormStateInterface $form_state)
  {
    $form = parent::buildform($form, $form_state);

    $form_state->setCached(FALSE);

    $heartbeat_type = $this->entity;
    $tokens = \Drupal::token()->getInfo();
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

//    if ($form_state->get('data_hidden') == NULL) $form_state->set('data_hidden', array());
//    $messageArguments = $form_state->get('data_hidden');
//
//    $argNum = count($messageArguments);
//
//    for ($i = 0; $i < $argNum; $i++) {
//
//      $form['variables']['variable'][$i] = array(
//        '#type' => 'textfield',
//        '#title' => t($messageArguments[$i]),
//        '#description' => t('Define message argument'),
//      );
//
//    }

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $heartbeat_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\heartbeat8\Entity\HeartbeatType::load',
      ],
      '#disabled' => !$heartbeat_type->isNew(),
    ];
    $z = 0;
    foreach ($tokens['tokens'] as $key => $type) {
      if (is_array($type)) {
        if (!is_array(current($type))) {

          $form[$key] = array(
            '#type' => 'details',
            '#title' => t((string)strtoupper($key)),
            '#collapsible' => TRUE,
            '#collapsed' => TRUE,
            '#states' => array(
              'expanded' => array(
                ':input[name="'.$key.'"]' => array('value' => 'expand'),
              ),
            ),
          );
          $s = 0;
          foreach ($type as $token) {
            if (!is_array($token)) {

              $form[$key][$token->title] = array(
                '#type' => 'item',
//                                    '#title' => t('token'),
                '#markup' => t((string)$token->title),
                '#attributes' => array('tabindex' => 20+$z)
              );
            } else {
              foreach ($token as $tkey => $subtoken) {

                $form[$tkey][is_array($subtoken) ? key($subtoken) : $subtoken] = array(
                  '#type' => 'details',
                  '#title' => t('token'),
                  '#collapsible' => TRUE,
                  '#collapsed' => TRUE,
                  '#states' => array(
                    ':input[name="'.is_array($subtoken) ? key($subtoken) : $subtoken.'"]' => array('value' => 'expand2'),
                  ));
              }
            }
            $s++;
          }
          ksort($form[$key]);
        } else {
          $form[$key] = array(
            '#type' => 'details',
            '#title' => t((string)strtoupper($key)),
            '#collapsible' => TRUE,
            '#collapsed' => TRUE,
            '#states' => array(
              'expanded' => array(
                ':input[name="'.$key.'"]' => array('value' => 'expand'),
              ),
            ),
          );
          foreach ($type as $skey => $subType) {
            if (is_array($subType)) {
              $form[$key][$skey] = array(
                '#type' => 'details',
                '#title' => t((string)$skey),
                '#collapsible' => TRUE,
                '#collapsed' => TRUE,
                '#states' => array(
                  'expanded' => array(
                    ':input[name="'.$skey.'"]' => array('value' => 'expand'),
                  ),
                ),
              );
              foreach ($subType as $vskey => $token) {
                if (!is_array($token)) {
                  $form[$key][$skey][$vskey] = array(
                    '#type' => 'item',
//                                            '#title' => t(is_array($token) ? $vskey : $token),
                    '#markup' => t(is_string($token) ? $token : is_string($vskey) ? $vskey : $key),
                    '#attributes' => array('tabindex' => 20+$z)
                  );
                } else {
                  $form[$key][$skey][$vskey] = array(
                    '#type' => 'details',
                    '#title' => $vskey,
                    '#collapsible' => TRUE,
                    '#collapsed' => TRUE,
                    '#states' => array(
                      'expanded' => array(
                        ':input[name="'.$vskey.'"]' => array('value' => 'expand'),
                      ),
                    ),
                  );
                  foreach ($token as $subKey => $subtoken) {
                    $form[$key][$skey][$vskey][is_array($subtoken->title) ? $subKey : $subtoken->title] = array(
                      '#type' => 'item',
                      '#markup' => t((string)is_array($subtoken) ? $subKey : $subtoken->title),
                    );
                  }
                }
              }
              ksort($form[$key][$skey]);
            }
          }
        }
      } else {
        $form[$key][$token == null ? 'null' : $token] = array(
          '#type' => 'details',
          '#title' => t($token == null ? 'null' : (string)$token),
          '#markup' => t($token == null ? 'null' : (string)$token),
        );
      }
      $z++;
    }


//    foreach ($data['storeTypes'] as $key => $type) {
//      if (is_array($type)) {
//        if (!is_array(current($type))) {
//
//    foreach ($tokens['tokens'] as $key => $value) {
//
//      if (is_array($value)) {
//        foreach ($value as $subKey => $subValue) {
//          if (is_array($subValue)) {
//            foreach ($subValue as $superKey => $superValue) {
//              if (is_array($superValue)) {
//                foreach($superValue as $microKey => $microValue) {
//                  if (is_array($microValue)) {
//                    \Drupal::logger()->debug("YOU NEED TO HANDLE CHILD TOKENS AT GREATER DEPTH");
//                  } else {
//
//                  }
//                }
//              }
//            }
//          }
//        }
//      }
//
//    }




//
//          $form[$key] = array(
//            '#type' => 'details',
//            '#title' => t(strtoupper($key)),
//            '#collapsible' => TRUE,
//            '#collapsed' => TRUE,
//            '#states' => array(
//              'expanded' => array(
//                ':input[name="' . $key . '"]' => array('value' => 'expand'),
//              ),
//            ),
//          );
//          $s = 0;
//          foreach ($type as $store) {
//            if (!is_array($store)) {
//
//              $form[$key][$store->title] = array(
//                '#type' => 'item',
//                //                                    '#title' => t('Store'),
//                '#markup' => t($store->title) . '<span class="hidden-nid">' . $store->nid . '</span>',
//                '#attributes' => array('tabindex' => 20 + $z)
//              );
//            } else {
//              foreach ($store as $key => $subStore) {
//
//                $form[$key][is_array($subStore) ? key($subStore) : $subStore] = array(
//                  '#type' => 'details',
//                  '#title' => t('Store'),
//                  '#collapsible' => TRUE,
//                  '#collapsed' => TRUE,
//                  '#states' => array(
//                    ':input[name="' . is_array($subStore) ? key($subStore) : $subStore . '"]' => array('value' => 'expand2'),
//                  ));
//              }
//            }
//          }

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

//    $form_state->set('data_hidden', $argsArray);

    $argNum = count($argsArray);

    for ($i = 0; $i < $argNum; $i++) {

      $form['variable' . $i] = array(
        '#type' => 'textfield',
        '#title' => t($argsArray[$i]),
        '#description' => t('Define message argument'),
      );

    }

    $form_state->setRebuild();

    return $form;

  }
}
