<?php

namespace Drupal\heartbeat8\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Heartbeat entity.
 *
 * @ingroup heartbeat8
 *
 * @ContentEntityType(
 *   id = "heartbeat",
 *   label = @Translation("Heartbeat"),
 *   bundle_label = @Translation("Heartbeat type"),
 *   handlers = {
 *     "storage" = "Drupal\heartbeat8\HeartbeatStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\heartbeat8\HeartbeatListBuilder",
 *     "views_data" = "Drupal\heartbeat8\Entity\HeartbeatViewsData",
 *     "translation" = "Drupal\heartbeat8\HeartbeatTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\heartbeat8\Form\HeartbeatForm",
 *       "add" = "Drupal\heartbeat8\Form\HeartbeatForm",
 *       "edit" = "Drupal\heartbeat8\Form\HeartbeatForm",
 *       "delete" = "Drupal\heartbeat8\Form\HeartbeatDeleteForm",
 *     },
 *     "access" = "Drupal\heartbeat8\HeartbeatAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\heartbeat8\HeartbeatHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "heartbeat",
 *   data_table = "heartbeat_field_data",
 *   revision_table = "heartbeat_revision",
 *   revision_data_table = "heartbeat_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer heartbeat entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/heartbeat/{heartbeat}",
 *     "add-page" = "/admin/structure/heartbeat/add",
 *     "add-form" = "/admin/structure/heartbeat/add/{heartbeat_type}",
 *     "edit-form" = "/admin/structure/heartbeat/{heartbeat}/edit",
 *     "delete-form" = "/admin/structure/heartbeat/{heartbeat}/delete",
 *     "version-history" = "/admin/structure/heartbeat/{heartbeat}/revisions",
 *     "revision" = "/admin/structure/heartbeat/{heartbeat}/revisions/{heartbeat_revision}/view",
 *     "revision_revert" = "/admin/structure/heartbeat/{heartbeat}/revisions/{heartbeat_revision}/revert",
 *     "translation_revert" = "/admin/structure/heartbeat/{heartbeat}/revisions/{heartbeat_revision}/revert/{langcode}",
 *     "revision_delete" = "/admin/structure/heartbeat/{heartbeat}/revisions/{heartbeat_revision}/delete",
 *     "collection" = "/admin/structure/heartbeat",
 *   },
 *   bundle_entity_type = "heartbeat_type",
 *   field_ui_base_route = "entity.heartbeat_type.edit_form"
 * )
 */
class Heartbeat extends RevisionableContentEntityBase implements HeartbeatInterface {

  use EntityChangedTrait;

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

    // If no revision author has been set explicitly, make the heartbeat owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
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
      ->setDescription(t('The user ID of author of the Heartbeat entity.'))
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
      ->setDescription(t('The name of the Heartbeat entity.'))
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
      ->setDescription(t('A boolean indicating whether the Heartbeat is published.'))
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

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * Returns the node type label for the passed node.
   *
   * @param \Drupal\heartbeat8\Entity\HeartbeatInterface $heartbeat
   *   A heartbeat entity to return the heartbeat type's label for.
   *
   * @return string|false
   *   The heartbeat type label or FALSE if the heartbeat type is not found.
   *
   * @todo Add this as generic helper method for config entities representing
   *   entity bundles.
   */
  public function heartbeat_get_type(HeartbeatInterface $heartbeat) {
    $type = HeartbeatType::load($heartbeat->bundle());
    return $type ? $type->label() : FALSE;
  }


  /**
   * Updates all heartbeat activities of one type to be of another type.
   *
   * @param string $old_id
   *   The current heartbeat type of the activities.
   * @param string $new_id
   *   The new heartbeat type of the activities.
   *
   * @return
   *   The number of activities whose heartbeat type field was modified.
   */
  function heartbeat_type_update_nodes($old_id, $new_id) {
    return \Drupal::entityManager()->getStorage('heartbeat')->updateType($old_id, $new_id);
  }

}
