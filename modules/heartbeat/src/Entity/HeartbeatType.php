<?php

namespace Drupal\heartbeat\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\heartbeat\HeartbeatTypeListBuilder;

/**
 * Defines the Heartbeat type entity.
 *
 * @ConfigEntityType(
 *   id = "heartbeat_type",
 *   label = @Translation("Heartbeat type"),
 *   handlers = {
 *     "list_builder" = "Drupal\heartbeat\HeartbeatTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\heartbeat\Form\HeartbeatTypeForm",
 *       "edit" = "Drupal\heartbeat\Form\HeartbeatTypeForm",
 *       "delete" = "Drupal\heartbeat\Form\HeartbeatTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\heartbeat\HeartbeatTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "heartbeat_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "heartbeat",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/heartbeat_type/{heartbeat_type}",
 *     "add-form" = "/admin/structure/heartbeat_type/add",
 *     "edit-form" = "/admin/structure/heartbeat_type/{heartbeat_type}/edit",
 *     "delete-form" = "/admin/structure/heartbeat_type/{heartbeat_type}/delete",
 *     "collection" = "/admin/structure/heartbeat_type"
 *   }
 * )
 */
class HeartbeatType extends ConfigEntityBundleBase implements HeartbeatTypeInterface {

  /**
   * The Heartbeat Stream ID.
   *
   * @var string
   */
  protected $id;
  protected $messageId;
  protected $hid;
  protected $description;
  protected $perms;
  protected $messageConcat;
  protected $arguments;
  protected $message;
  protected $variables;
  protected $attachments;
  protected $groupType;
  protected $mainentity;


  protected $entityManager;

  /**
   * The Heartbeat Stream label.
   *
   * @var string
   */
  protected $label;


  public static function getHeartbeatTypeEntity($messageId) {
    $entity_manager = \Drupal::entityTypeManager();
  }

  public function setMessageId($messageId) {
    $this->messageId = $messageId;
  }

  public function getMessageId() {
    return $this->messageId;
  }

  /**
   * Sets the description of the stream
   *
   * @param string $description
   *  Describing streams of this type
   */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
   * Gets the description of the stream
   *
   * @return string
   *  The Stream's description
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * Sets the translatable message
   * This message creates the structure of each message
   *
   * @param string $message
   *  The template message serving as the foundation of each message structure of this stream type
   */
  public function setMessage($message) {
    $this->set('message', $message);
  }

  /**
   * Gets the translatable message of the stream
   *
   * @return string
   *  The Stream's message
   */
  public function getMessage() {
    return $this->get('message');
  }

//  /**
//   * Sets the translatable concatenated message
//   *
//   * @param string $messageConcat
//   *
//   */
//  public function setMessageConcat($messageConcat) {
//    $this->messageConcat = $messageConcat;
//  }
//
//  /**
//   * Gets the concatenated message of the stream
//   *
//   * @return string
//   *  The Stream's concatenated message
//   */
//  public function getMessageConcat() {
//    return $this->messageConcat;
//  }

  /**
   * Sets the Permissions for this message stream
   *
   * @param int $perms
   *
   */
  public function setPerms($perms) {
    $this->perms = $perms;
  }

  /**
   * Gets the Permissions of this message stream
   *
   * @return int
   *  The stream's permissions
   */
  public function getPerms() {
    return $this->perms;
  }

  /**
   * Sets the Group Type for this message stream
   *
   * @param string $groupType
   *
   */
  public function setGroupType($groupType) {
    $this->groupType = $groupType;
  }

  /**
   * Gets the Group Type of this message stream
   *
   * @return string
   *  The stream's Group Type
   */
  public function getGroupType() {
    return $this->groupType;
  }

  /**
   * Sets the arguments for the concatenated message
   *
   * @param string $arguments
   *
   */
  public function setArguments($arguments) {
    $this->set('arguments', $arguments);
//    $this->arguments = $arguments;
  }

  /**
   * Gets the arguments for the concatenated message
   *
   * @return string
   *  The stream's arguments for the concatenated message
   */
  public function getArguments() {
    return $this->get('arguments');
//    return $this->arguments;
  }

  /**
   * Sets the variables for this message stream
   *
   * @param string $variables
   *
   */
  public function setVariables($variables) {
    $this->variables = $variables;
  }

  /**
   * Gets the variables of this message stream
   *
   * @return string
   *  The stream's variables
   */
  public function getVariables() {
    return $this->variables;
  }

  /**
   * @inheritDoc
   */
  public function __construct(array $values, $entity_type)
  {
    parent::__construct($values, $entity_type);
    $this->entityManager = \Drupal::entityManager();
    $this->entityTypeManager = \Drupal::entityTypeManager();
  }

  /**
   * @inheritDoc
   */
  protected function entityManager(){
    return parent::entityManager();
  }

  /**
   * @inheritDoc
   */
  protected function entityTypeManager() {
    return parent::entityTypeManager();
  }


  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    $locked = \Drupal::state()->get('heartbeat.type.locked');
    return isset($locked[$this->id()]) ? $locked[$this->id()] : FALSE;
  }


  /**
   *
   */

  public function loadHeartbeatType() {
    $this->entityTypeManager->getDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if ($update && $this->getOriginalId() != $this->id()) {
      $update_count = heartbeat_type_update_heartbeats($this->getOriginalId(), $this->id());
      if ($update_count) {
        drupal_set_message(\Drupal::translation()->formatPlural($update_count,
          'Changed the heartbeat type of 1 activity from %old-type to %type.',
          'Changed the heartbeat type of @count activities from %old-type to %type.',
          [
            '%old-type' => $this->getOriginalId(),
            '%type' => $this->id(),
          ]));
      }
    }
    if ($update) {
      // Clear the cached field definitions as some settings affect the field
      // definitions.
      $this->entityManager()->clearCachedFieldDefinitions();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    // Clear the heartbeat type cache to reflect the removal.
    $storage->resetCache(array_keys($entities));
  }

  /**
   * Sets the main Entity Type of the Heartbeat Type
   *
   * @param string $mainentity
   *  Describing entity type used in this Heartbeat Type
   */
  public function setMainEntity($mainentity) {
    $this->set('mainentity', $mainentity);
  }

  /**
   * @return mixed|null
   *
   */
  public function getMainEntity() {
    return $this->get('mainentity');
  }


  /**
   * Sets the bundle targeted for this Heartbeat type
   *
   * @param string $variables
   *
   */
  public function setBundle($bundle) {
    $this->set('bundle', $bundle);
  }

  /**
   * Gets the bundle of this Heartbeat type
   *
   * @return string
   *  The stream's variables
   */
  public function getBundle() {
    return $this->get('bundle');
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
