<?php

namespace Drupal\heartbeat\Form;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Render\Renderer;
use Drupal\heartbeat;
use Drupal\heartbeat\Entity;
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
 * @package Drupal\heartbeat\Form
 */
class HeartbeatTypeForm extends EntityForm {

  protected $treeBuilder;

  protected $renderer;

  private $tokenTree;

  protected $entityTypeManager;

  protected $entityTypes;

  private $treeAdded = false;

  private $messageMap = array();


  /**
   * {@inheritdoc}
   * @throws \Exception
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('token.tree_builder'),
      $container->get('renderer'),
      $container->get('entity_type.manager'));
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
  public function __construct(TreeBuilder $tree_builder, Renderer $renderer, EntityTypeManager $entityTypeManager) {


    $this->treeBuilder = $tree_builder;
    $this->renderer = $renderer;
    $this->entityTypeManager = $entityTypeManager;

    $this->tokenTree = $this->renderer->render($this->treeBuilder->buildAllRenderable([
      'click_insert' => TRUE,
      'show_restricted' => TRUE,
      'show_nested' => FALSE,
    ]));

  }


  public function buildForm(array $form, FormStateInterface $form_state) {

    $this->entityTypes = Entity\Heartbeat::getEntityNames($this->entityTypeManager->getDefinitions());
    $doStuff = 'stuff';

    return parent::buildForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form_state->setCached(FALSE);

    $heartbeat_type = $this->entity;
    $form['#tree'] = TRUE;

    $form['#attached']['library'] = 'heartbeat/treeTable';

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


    $form['entity_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Entity Type'),
//      '#default_value' => $heartbeat_type->getEntityType(),
      '#description' => $this->t("Primary Entity Type for this Heartbeat Type"),
      '#options' => array($this->entityTypes
      ),
      '#required' => TRUE,
//      '#ajax' => [
//        'callback' => '::getBundlesForEntity',
//        'event' => 'change',
//        'progress' => array(
//          'type' => 'throbber',
//          'message' => t('Getting bundles'),
//        ),
//      ],
//      '#submit' => array('::getBundlesForEntity'),
    );

    $bundles = $form_state->get('entity_bundles');

    $form['entity_bundles'] = array(
      '#type' => 'container',
      '#prefix' => '<div id="entity-bundles">',
      '#suffix' => '</div>'
    );

    $form['entity_bundles']['getBundles'] = [
      '#type' => 'submit',
      '#value' => t('Getting bundles'),
      '#submit' => array('::getBundlesForEntity'),
      '#ajax' => [
        'callback' => '::getBundlesForEntity',
        'wrapper' => 'entity-bundles',
        'progress' => array(
          'type' => 'throbber',
          'message' => t('Getting bundles'),
        ),
      ],
    ];

    $form['entity_bundles']['list'] = array(
      '#type' => 'select',
      '#title' => $this->t('Entity Bundles'),
//      '#default_value' => $heartbeat_type->getEntityType(),
      '#description' => $this->t("Any bundles available to the specified entity"),
      '#options' => $bundles,
    );


    $form['message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('message'),
      '#maxlength' => 255,
      '#default_value' => $heartbeat_type->getMessage(),
      '#description' => $this->t("The structure for messages of this type. Use !exclamation marks before fields and entities"),
      '#required' => TRUE,
    );


//    $form['message_concat'] = array(
//      '#type' => 'textfield',
//      '#title' => $this->t('Message structure in concatenated form'),
//      '#maxlength' => 255,
//      '#default_value' => $heartbeat_type->getMessageConcat(),
//      '#description' => $this->t("The structure for messages of this type. Use !exclamation marks before fields and entities"),
//      '#required' => FALSE,
//    );


    $form['perms'] = array(
      '#type' => 'select',
      '#title' => $this->t('Permissions'),
      '#default_value' => $heartbeat_type->getPerms(),
      '#description' => $this->t("Default permissions to view Heartbeats of this type"),
      '#options' => array(
        HEARTBEAT_NONE => 'None',
        HEARTBEAT_PRIVATE => 'Private',
        HEARTBEAT_PUBLIC_TO_ADDRESSEE => 'Public to Addressee',
        HEARTBEAT_PUBLIC_TO_CONNECTED => 'Public to Connected',
        HEARTBEAT_PUBLIC_TO_ALL => 'Public to All',

      ),
      '#required' => TRUE,
    );


    $form['group_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Group Type'),
      '#default_value' => 0,
      '#description' => $this->t("Type of group associated with Heartbeats of this type"),
      '#options' => array(
        HEARTBEAT_GROUP_NONE => 'None',
        HEARTBEAT_GROUP_SINGLE =>'Single',
        HEARTBEAT_GROUP_SUMMARY => 'Group',
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
          '#default_value' => $variableValue,
//          '#ajax' => !$this->treeAdded ? [
//            'callback' => '::tokenSelectDialog',
//            'event' => 'focus',
//            'progress' => array(
//              'type' => 'throbber',
//              'message' => t('Rebuilding arguments'),
//            ),
//          ] : [],
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
        'exists' => '\Drupal\heartbeat\Entity\HeartbeatType::load',
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
$bundleArray = $form_state->get('entity_bundles');
$bundleChoice = $form_state->getValue('entity_bundles');
    $heartbeat_type->set('description', $form_state->getValue('description'));
    $heartbeat_type->set('message', $form_state->getValue('message'));
    $heartbeat_type->set('perms', $form_state->getValue('perms'));
    $heartbeat_type->set('variables', $form_state->getValue('variables'));
    $heartbeat_type->set('arguments', json_encode($form_state->get('messageMap')));
    $heartbeat_type->set('mainentity', $this->entityTypes[$form_state->getValue('entity_type')]);
    $heartbeat_type->set('bundle', $form_state->get('entity_bundles')[$form_state->getValue('entity_bundles')['list']]);

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

    if ($form_state != NULL) {
      $argsArray = $this->extractMessageArguments($messageArgString, $form_state);

      foreach ($argsArray as $key => $arg) {
        $this->messageMap[$key] = '!' . $arg;
      }

      $form_state->set('messageMapKey', $this->messageMap);
      $form_state->set('data_hidden', $argsArray);
      $form_state->setRebuild();

      return $form['variables'];

    } else {
      return NULL;
    }
  }

  public function prepareVariables(&$form, FormStateInterface $form_state) {
    return $form['variables'];
  }


  private function extractMessageArguments($message) {
//TODO find solution for trailing exclamation marks being wrongly interpreted
    //ie parse each word in string and reconstruct string prior to exploding it on
    //exclamation marks again
    $messageArguments = array_slice(explode('!', $message), 1);

    $argsArray = array();

    foreach ($messageArguments as $argument) {

      if (strlen($argument) > 0) {

        $cleanArgument = strpos($argument, ' ') ? substr($argument, 0, strpos($argument, ' ')) : $argument;
        $argsArray[] = $cleanArgument;
        $this->messageMap[] = '!' . $cleanArgument;

      }
    }

    return $argsArray;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $messageMapKeysget = $form_state->get('messageMapKey');

    if ($variables = $form_state->getValue('variables')) {

      $num = count($variables);

      for ($i = 0; $i < $num; $i++) {
        if (!is_string($variables[$i])) { continue; }
          $this->messageMap[$messageMapKeysget[$i]] = $variables[$i];
      }

      $form_state->set('messageMap', $this->messageMap);

      parent::submitForm($form, $form_state);

    }
  }

  /**
   * Custom form validation to rebuild
   * Form field for mapping Message Arguments
   */

  public function getBundlesForEntity(array &$form, FormStateInterface $form_state) {

    $entityType = $this->entityTypes[$form_state->getValue('entity_type')];

    $entity = $this->entityTypeManager->getStorage($entityType);
    $bundleTypeName = $entity->getEntityType()->getBundleEntityType();
    $bundles = $this->entityTypeManager->getStorage($bundleTypeName)->loadMultiple();
    $bundleNames = array();

    foreach ($bundles as $bundle) {
      $bundleNames[] = $bundle->id();
    }

    $form_state->set('entity_bundles', $bundleNames);
//    $form['entity_bundles']['#options'] = array($bundleNames);
    $form_state->setRebuild();

    return $form['entity_bundles'];

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


  public function getBundlesAlternate(array &$form, FormStateInterface $form_state) {
    $ajax_response = new AjaxResponse();

    $AddChartForm = \Drupal::formBuilder()->getForm('Drupal\heartbeat\Form\HeartbeatTypeForm');

    $ajax_response->addCommand(new HtmlCommand('#formarea', $AddChartForm));

  }

}
