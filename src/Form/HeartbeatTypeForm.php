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

  private $treeAdded = false;



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


  public function buildForm(array $form, FormStateInterface $form_state) {

    $doStuff = 'stuff';
    return parent::buildForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

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


    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('description'),
      '#maxlength' => 255,
      '#default_value' => $heartbeat_type->getDescription(),
      '#description' => $this->t("Description of the Heartbeat Type"),
      '#required' => TRUE,
    );


    $form['message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('message'),
      '#maxlength' => 255,
      '#default_value' => $heartbeat_type->getMessage(),
      '#description' => $this->t("The structure for messages of this type. Use !exclamation marks before fields and entities"),
      '#required' => TRUE,
    );


    $form['message_concat'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Message structure in concatenated form'),
      '#maxlength' => 255,
      '#default_value' => $heartbeat_type->getMessageConcat(),
      '#description' => $this->t("The structure for messages of this type. Use !exclamation marks before fields and entities"),
      '#required' => FALSE,
    );


    $form['perms'] = array(
      '#type' => 'select',
      '#title' => $this->t('Permissions'),
      '#default_value' => $heartbeat_type->getPerms(),
      '#description' => $this->t("Default permissions to view Heartbeats of this type"),
      '#options' => array(
        heartbeat8\HEARTBEAT_NONE => 'None',
        heartbeat8\HEARTBEAT_PRIVATE => 'Private',
        heartbeat8\HEARTBEAT_PUBLIC_TO_ADDRESSEE => 'Public to Addressee',
        heartbeat8\HEARTBEAT_PUBLIC_TO_CONNECTED => 'Public to Connected',
        heartbeat8\HEARTBEAT_PUBLIC_TO_ALL => 'Public to All',

      ),
      '#required' => TRUE,
    );


    $form['group_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Group Type'),
      '#default_value' => 0,
      '#description' => $this->t("Type of group associated with Heartbeats of this type"),
      '#options' => array(
        heartbeat8\HEARTBEAT_GROUP_NONE => 'None',
        heartbeat8\HEARTBEAT_GROUP_SINGLE =>'Single',
        heartbeat8\HEARTBEAT_GROUP_SUMMARY => 'Group',
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

    if ($messageArguments === NULL) {
      $messageArguments = $this->extractMessageArguments($heartbeat_type->getMessage());
    }

    $argNum = count($messageArguments);

    for ($i = 0; $i < $argNum; $i++) {

      if (is_array($messageArguments) && $messageArguments[$i] != null) {

        $variableValue =
          isset($heartbeat_type->getVariables()[$i])
          &&
          !empty($heartbeat_type->getVariables()[$i])
            ?
          $heartbeat_type->getVariables()[$i] : '';

        $form['variables'][$i] = array(
          '#type' => 'textfield',
          '#title' => t($messageArguments[$i]),
          '#description' => t('Map value to this variable'),
          '#default_value' =>$variableValue,
          '#ajax' => !$this->treeAdded ? [
            'callback' => '::tokenSelectDialog',
            'event' => 'focus',
            'progress' => array(
              'type' => 'throbber',
              'message' => t('Rebuilding arguments'),
            ),
          ] : [],
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

    $form_state->setCached(FALSE);

    return parent::form($form, $form_state, $heartbeat_type);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $heartbeat_type = $this->entity;

    $heartbeat_type->set('description', $form_state->getValue('description'));
    $heartbeat_type->set('message', $form_state->getValue('message'));
    $heartbeat_type->set('perms', $form_state->getValue('perms'));
    $heartbeat_type->set('variables', $form_state->getValue('variables'));
//    $heartbeat_type
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

    $argsArray = $this->extractMessageArguments($messageArgString);

    $form_state->set('data_hidden', $argsArray);
    $form_state->setRebuild();

    return $form['variables'];

  }

  public function prepareVariables(&$form, FormStateInterface $form_state) {
    return $form['variables'];
  }


  private function extractMessageArguments($message) {
    $messageArguments = array_slice(explode('!', $message), 1);

    $argsArray = array();

    foreach ($messageArguments as $argument) {

      if (strlen($argument) > 0) {

        $cleanArgument = strpos($argument, ' ') ? substr($argument, 0, strpos($argument, ' ')) : $argument;
        $argsArray[] = $cleanArgument;

      }
    }
    return $argsArray;
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
    $ajax_response->addCommand(new InvokeCommand('.token-tree', 'css', array('display', 'block')));
    $this->treeAdded = TRUE;
    // Return the AjaxResponse Object.
    return $ajax_response;
  }

}
