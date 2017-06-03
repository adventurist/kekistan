<?php

namespace Drupal\heartbeat\Entity;

use Drupal\heartbeat\Entity\HeartbeatType;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Heartbeat stream entity.
 *
 * @ingroup heartbeat
 *
 * @ContentEntityType(
 *   id = "heartbeat_stream",
 *   label = @Translation("Heartbeat stream"),
 *   handlers = {
 *     "storage" = "Drupal\heartbeat\HeartbeatStreamStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\heartbeat\HeartbeatStreamListBuilder",
 *     "views_data" = "Drupal\heartbeat\Entity\HeartbeatStreamViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\heartbeat\Form\HeartbeatStreamForm",
 *       "add" = "Drupal\heartbeat\Form\HeartbeatStreamForm",
 *       "edit" = "Drupal\heartbeat\Form\HeartbeatStreamForm",
 *       "delete" = "Drupal\heartbeat\Form\HeartbeatStreamDeleteForm",
 *     },
 *     "access" = "Drupal\heartbeat\HeartbeatStreamAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\heartbeat\HeartbeatStreamHtmlRouteProvider",
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
  protected $weight;

  /**
   * @return array
   */
  public function getTypes() {
    return $this->get('types');
  }

  /**
   * @param array use Drupal\heartbeat\Entity\HeartbeatType $types
   */
  public function setTypes($types) {
    $this->set('types', $types);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getClass() {
    return $this->get('class');
  }

  /**
   * @param mixed $class
   */
  public function setClass($class) {
    $this->set('class', $class);
  }

  /**
   * @return mixed
   */
  public function getRealClass() {
    return $this->get('realClass');
  }

  /**
   * @param mixed $realClass
   */
  public function setRealClass($realClass) {
    $this->set('realClass', $realClass);
  }

  /**
   * @return mixed
   */
  public function getModule() {
    return $this->get('module');
  }

  /**
   * @param mixed $module
   */
  public function setModule($module) {
    $this->set('module', $module);
  }

  /**
   * @return mixed
   */
  public function getTitle() {
    return $this->get('title');
  }

  /**
   * @param mixed $title
   */
  public function setTitle($title) {
    $this->set('title', $title);
  }

  /**
   * @return mixed
   */
  public function getPath() {
    return $this->get('path');
  }

  /**
   * @param mixed $path
   */
  public function setPath($path) {
    $this->set('path', $path);
  }

  /**
   * @return mixed
   */
  public function getSettings() {
    return $this->get('settings');
  }

  /**
   * @param mixed $settings
   */
  public function setSettings($settings) {
    $this->set('settings ', $settings);
  }

  /**
   * @return mixed
   */
  public function getVariables() {
    return $this->get('variables');
  }

  /**
   * @param mixed $variables
   */
  public function setVariables($variables) {
    $this->set('variables', $variables);
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

    $fields['path'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Path'))
      ->setSettings(array(
        'max_length' => 255,
      ));

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
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'heartbeat_type');

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('The weight of the stream'))
      ->setDefaultValue(0);


    return $fields;
  }

  /**
   * @return mixed
   */
  public function setWeight($weight) {
    $this->set('weight', $weight);
  }

  /**
   * @param $weight
   * @return mixed
   */
  public function getWeight() {
    return $this->get('weight');
  }
}
