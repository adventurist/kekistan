<?php

namespace Drupal\heartbeat8\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\heartbeat8\HeartbeatTypeServices;
use Drupal\heartbeat8\Entity\HeartbeatStream;
use Drupal\heartbeat8\Entity\HeartbeatType;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Heartbeat stream edit forms.
 *
 * @ingroup heartbeat8
 */
class HeartbeatStreamForm extends ContentEntityForm {

  protected $heartbeatTypeService;



  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('heartbeat8.heartbeattype'),
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
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
  public function __construct(HeartbeatTypeServices $heartbeatTypeService, EntityManager $entityManager) {
    parent::__construct($entityManager);
    $this->heartbeatTypeService = $heartbeatTypeService;

  }




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

    $form['types'] = array(

      '#type' => 'checkboxes',
      '#options' => $this->heartbeatTypeService->getTypes(),

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

    if ($entity instanceof HeartbeatStream) {

      foreach ($form_state->getValue('types') as $type) {
        $entity->set('types', $type);
      }
      $entity->save();
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
