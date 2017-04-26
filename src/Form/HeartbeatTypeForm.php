<?php

namespace Drupal\heartbeat8\Form;

use Drupal\Core\Render\Renderer;
use Drupal\heartbeat8;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\token\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HeartbeatTypeForm.
 *
 * @property \Drupal\Component\Render\MarkupInterface|string tokenTree
 * @package Drupal\heartbeat8\Form
 */
class HeartbeatTypeForm extends EntityForm {

  protected $treeBuilder;

  protected $renderer;

  private $tokenTree;



  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('token.tree_builder'), $container->get('renderer'));
  }


  /**
   * PHP 5 allows developers to declare constructor methods for classes.
   * Classes which have a constructor method call this method on each newly-created object,
   * so it is suitable for any initialization that the object may need before it is used.
   *
   * Note: Parent constructors are not called implicitly if the child class defines a constructor.
   * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
   *
   * param [ mixed $args [, $... ]]
   * @param TreeBuilder $tree_builder
   * @param Renderer $renderer
   * @link http://php.net/manual/en/language.oop5.decon.php
   * @throws \Exception
   */
  public function __construct(TreeBuilder $tree_builder, Renderer $renderer) {


    $this->treeBuilder = $tree_builder;
    $this->renderer = $renderer;

    $this->tokenTree = $this->renderer->render($this->treeBuilder->buildAllRenderable([
      'click_insert' => TRUE,
      'show_restricted' => TRUE,
      'show_nested' => FALSE,
    ]));

  }



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

    $form['#attached']['library'] = 'heartbeat8/treeTable';

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
      '#prefix' => '<div id="Variables-fieldset-wrapper">',
      '#suffix' => '</div>',
    );

    $messageArguments = $form_state->get('data_hidden');

    if ($messageArguments === NULL) { $messageArguments = $form_state->set('data_hidden', array()); }

    $argNum = count($messageArguments);

    for ($i = 0; $i < $argNum; $i++) {

      if (is_array($messageArguments) && $messageArguments[$i] != null) {

        $form['variables'][$i] = array(
          '#type' => 'textfield',
          '#title' => t($messageArguments[$i]),
          '#description' => t('Map value to this variable'),
          '#ajax' => [
            'callback' => '::tokenSelectDialog',
            'event' => 'focus',
            'progress' => array(
              'type' => 'throbber',
              'message' => t('Rebuilding arguments'),
            ),
          ]
        );

      }
    }

    $form['variables']['rebuildArgs'] = [
      '#type' => 'submit',
      '#value' => t('Rebuild Arguments'),
      '#submit' => array('::rebuildMessageArguments'),
      '#ajax' => [
        'callback' => '::rebuildMessageArguments',
        'wrapper' => 'Variables-fieldset-wrapper',
      ],
    ];

    $form['tokens'] = array(
      '#prefix' => '<div id="token-tree"></div>',
      '#markup' => $this->tokenTree
    );

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $heartbeat_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\heartbeat8\Entity\HeartbeatType::load',
      ],
      '#disabled' => !$heartbeat_type->isNew(),
    ];


    //Build temporary token form for developmental assistance
    $z = 0;
//    foreach ($tokens['tokens'] as $key => $type) {
//      if (is_array($type)) {
//        if (!is_array(current($type))) {
//
//          $form[$key] = array(
//            '#type' => 'details',
//            '#title' => t((string)strtoupper($key)),
//            '#collapsible' => TRUE,
//            '#collapsed' => TRUE,
//            '#states' => array(
//              'expanded' => array(
//                ':input[name="'.$key.'"]' => array('value' => 'expand'),
//              ),
//            ),
//          );
//          $s = 0;
//          foreach ($type as $token) {
//            if (!is_array($token)) {
//
//              $form[$key][$token->title] = array(
//                '#type' => 'item',
//                '#markup' => t((string)$token->title),
//                '#attributes' => array('tabindex' => 20+$z)
//              );
//            } else {
//              foreach ($token as $tkey => $subtoken) {
//
//                $form[$tkey][is_array($subtoken) ? key($subtoken) : $subtoken] = array(
//                  '#type' => 'details',
//                  '#title' => t('token'),
//                  '#collapsible' => TRUE,
//                  '#collapsed' => TRUE,
//                  '#states' => array(
//                    ':input[name="'.is_array($subtoken) ? key($subtoken) : $subtoken.'"]' => array('value' => 'expand2'),
//                  ));
//              }
//            }
//            $s++;
//          }
//          ksort($form[$key]);
//        } else {
//          $form[$key] = array(
//            '#type' => 'details',
//            '#title' => t((string)strtoupper($key)),
//            '#collapsible' => TRUE,
//            '#collapsed' => TRUE,
//            '#states' => array(
//              'expanded' => array(
//                ':input[name="'.$key.'"]' => array('value' => 'expand'),
//              ),
//            ),
//          );
//          foreach ($type as $skey => $subType) {
//            if (is_array($subType)) {
//              $form[$key][$skey] = array(
//                '#type' => 'details',
//                '#title' => t((string)$skey),
//                '#collapsible' => TRUE,
//                '#collapsed' => TRUE,
//                '#states' => array(
//                  'expanded' => array(
//                    ':input[name="'.$skey.'"]' => array('value' => 'expand'),
//                  ),
//                ),
//              );
//              foreach ($subType as $vskey => $token) {
//                if (!is_array($token)) {
//                  $form[$key][$skey][$vskey] = array(
//                    '#type' => 'item',
////                                            '#title' => t(is_array($token) ? $vskey : $token),
//                    '#markup' => t(is_string($token) ? $token : is_string($vskey) ? $vskey : $key),
//                    '#attributes' => array('tabindex' => 20+$z)
//                  );
//                } else {
//                  $form[$key][$skey][$vskey] = array(
//                    '#type' => 'details',
//                    '#title' => $vskey,
//                    '#collapsible' => TRUE,
//                    '#collapsed' => TRUE,
//                    '#states' => array(
//                      'expanded' => array(
//                        ':input[name="'.$vskey.'"]' => array('value' => 'expand'),
//                      ),
//                    ),
//                  );
//                  foreach ($token as $subKey => $subtoken) {
//                    $form[$key][$skey][$vskey][is_array($subtoken->title) ? $subKey : $subtoken->title] = array(
//                      '#type' => 'item',
//                      '#markup' => t((string)is_array($subtoken) ? $subKey : $subtoken->title),
//                    );
//                  }
//                }
//              }
//              ksort($form[$key][$skey]);
//            }
//          }
//        }
//      } else {
//        $form[$key][$token == null ? 'null' : $token] = array(
//          '#type' => 'details',
//          '#title' => t($token == null ? 'null' : (string)$token),
//          '#markup' => t($token == null ? 'null' : (string)$token),
//        );
//      }
//      $z++;
//    }

    $form_state->setCached(FALSE);

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

    $messageArgString = $form_state->getValue('message');
    $messageArguments = array_slice(explode('!', $messageArgString), 1);

    $argsArray = array();

    foreach ($messageArguments as $argument) {

      if (strlen($argument) > 0) {

        $cleanArgument = strpos($argument, ' ') ? substr($argument, 0, strpos($argument, ' ')) : $argument;
        $argsArray[] = $cleanArgument;

      }
    }

    $form_state->set('data_hidden', $argsArray);
    $form_state->setRebuild();

    return $form['variables'];

  }

  public function prepareVariables(&$form, FormStateInterface $form_state) {
    return $form['variables'];
  }


  public function tokenSelectDialog(array &$form, FormStateInterface $form_state) {
    // Instantiate an AjaxResponse Object to return.
    $ajax_response = new AjaxResponse();


    // Add a command to execute on form, jQuery .html() replaces content between tags.
    // In this case, we replace the description with whether the username was found or not.
//    $ajax_response->addCommand(new HtmlCommand('#token-tree', $output));

    // CssCommand did not work.
    //$ajax_response->addCommand(new CssCommand('#edit-user-name--description', array('color', $color)));
//    $color = 'pink';
    // Add a command, InvokeCommand, which allows for custom jQuery commands.
    // In this case, we alter the color of the description.
    $ajax_response->addCommand(new InvokeCommand('#token-tree', 'css', array('display', 'block')));

    // Return the AjaxResponse Object.
    return $ajax_response;
  }

}
