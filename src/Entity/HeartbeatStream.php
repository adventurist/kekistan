<?php

namespace Drupal\heartbeat8\Entity;

use Drupal\heartbeat8\Entity\HeartbeatType;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Heartbeat stream entity.
 *
 * @ingroup heartbeat8
 *
 * @ContentEntityType(
 *   id = "heartbeat_stream",
 *   label = @Translation("Heartbeat stream"),
 *   handlers = {
 *     "storage" = "Drupal\heartbeat8\HeartbeatStreamStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\heartbeat8\HeartbeatStreamListBuilder",
 *     "views_data" = "Drupal\heartbeat8\Entity\HeartbeatStreamViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\heartbeat8\Form\HeartbeatStreamForm",
 *       "add" = "Drupal\heartbeat8\Form\HeartbeatStreamForm",
 *       "edit" = "Drupal\heartbeat8\Form\HeartbeatStreamForm",
 *       "delete" = "Drupal\heartbeat8\Form\HeartbeatStreamDeleteForm",
 *     },
 *     "access" = "Drupal\heartbeat8\HeartbeatStreamAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\heartbeat8\HeartbeatStreamHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "heartbeat_stream",
 *   revision_table = "heartbeat_stream_revision",
 *   revision_data_table = "heartbeat_stream_field_revision",
 *   admin_permission = "administer heartbeat stream entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *     "types" = "types",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/heartbeatstream/heartbeat_stream/{heartbeat_stream}",
 *     "add-form" = "/admin/structure/heartbeatstream/heartbeat_stream/add",
 *     "edit-form" = "/admin/structure/heartbeatstream/heartbeat_stream/{heartbeat_stream}/edit",
 *     "delete-form" = "/admin/structure/heartbeatstream/heartbeat_stream/{heartbeat_stream}/delete",
 *     "version-history" = "/admin/structure/heartbeatstream/heartbeat_stream/{heartbeat_stream}/revisions",
 *     "revision" = "/admin/structure/heartbeatstream/heartbeat_stream/{heartbeat_stream}/revisions/{heartbeat_stream_revision}/view",
 *     "revision_delete" = "/admin/structure/heartbeatstream/heartbeat_stream/{heartbeat_stream}/revisions/{heartbeat_stream_revision}/delete",
 *     "collection" = "/admin/structure/heartbeatstream/heartbeat_stream",
 *   },
 *   field_ui_base_route = "heartbeat_stream.settings"
 * )
 */

class HeartbeatStream extends RevisionableContentEntityBase implements HeartbeatStreamInterface {

  use EntityChangedTrait;


  protected $class;
  protected $realClass;
  protected $name;
  protected $module;
  protected $title;
  protected $path;
  protected $settings;
  protected $variables;
  protected $types;

  /**
   * @return array
   */
  public function getTypes() {
    return $this->types;
  }

  /**
   * @param array use Drupal\heartbeat8\Entity\HeartbeatType $types
   */
  public function setTypes($types) {
    $this->types = $types;
  }

  /**
   * @return mixed
   */
  public function getClass() {
    return $this->class;
  }

  /**
   * @param mixed $class
   */
  public function setClass($class) {
    $this->class = $class;
  }

  /**
   * @return mixed
   */
  public function getRealClass() {
    return $this->realClass;
  }

  /**
   * @param mixed $realClass
   */
  public function setRealClass($realClass) {
    $this->realClass = $realClass;
  }

  /**
   * @return mixed
   */
  public function getModule() {
    return $this->module;
  }

  /**
   * @param mixed $module
   */
  public function setModule($module) {
    $this->module = $module;
  }

  /**
   * @return mixed
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * @param mixed $title
   */
  public function setTitle($title) {
    $this->title = $title;
  }

  /**
   * @return mixed
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * @param mixed $path
   */
  public function setPath($path) {
    $this->path = $path;
  }

  /**
   * @return mixed
   */
  public function getSettings() {
    return $this->settings;
  }

  /**
   * @param mixed $settings
   */
  public function setSettings($settings) {
    $this->settings = $settings;
  }

  /**
   * @return mixed
   */
  public function getVariables() {
    return $this->variables;
  }

  /**
   * @param mixed $variables
   */
  public function setVariables($variables) {
    $this->variables = $variables;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the heartbeat_stream owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionCreationTime() {
    return $this->get('revision_timestamp')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionCreationTime($timestamp) {
    $this->set('revision_timestamp', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionUser() {
    return $this->get('revision_uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionUserId($uid) {
    $this->set('revision_uid', $uid);
    return $this;
  }



  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Heartbeat stream entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Heartbeat stream entity.'))
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Heartbeat stream is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_timestamp'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Revision timestamp'))
      ->setDescription(t('The time that the current revision was created.'))
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Revision user ID'))
      ->setDescription(t('The user ID of the author of the current revision.'))
      ->setSetting('target_type', 'user')
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['types'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Heartbeat Types'))
      ->setDescription(t('The Heartbeat Types included in this stream'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'heartbeat_type')
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED);


    return $fields;
  }

}
