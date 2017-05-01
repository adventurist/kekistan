<?php

namespace Drupal\heartbeat8\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Heartbeat stream revision.
 *
 * @ingroup heartbeat8
 */
class HeartbeatStreamRevisionDeleteForm extends ConfirmFormBase {


  /**
   * The Heartbeat stream revision.
   *
   * @var \Drupal\heartbeat8\Entity\HeartbeatStreamInterface
   */
  protected $revision;

  /**
   * The Heartbeat stream storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $HeartbeatStreamStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new HeartbeatStreamRevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityStorageInterface $entity_storage, Connection $connection) {
    $this->HeartbeatStreamStorage = $entity_storage;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_manager = $container->get('entity.manager');
    return new static(
      $entity_manager->getStorage('heartbeat_stream'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'heartbeat_stream_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the revision from %revision-date?', array('%revision-date' => format_date($this->revision->getRevisionCreationTime())));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.heartbeat_stream.version_history', array('heartbeat_stream' => $this->revision->id()));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $heartbeat_stream_revision = NULL) {
    $this->revision = $this->HeartbeatStreamStorage->loadRevision($heartbeat_stream_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->HeartbeatStreamStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Heartbeat stream: deleted %title revision %revision.', array('%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()));
    drupal_set_message(t('Revision from %revision-date of Heartbeat stream %title has been deleted.', array('%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label())));
    $form_state->setRedirect(
      'entity.heartbeat_stream.canonical',
       array('heartbeat_stream' => $this->revision->id())
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {heartbeat_stream_field_revision} WHERE id = :id', array(':id' => $this->revision->id()))->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.heartbeat_stream.version_history',
         array('heartbeat_stream' => $this->revision->id())
      );
    }
  }

}
