<?php

namespace Drupal\heartbeat8\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;


/**
 * Defines the Heartbeat stream entity entity.
 *
 * @ConfigEntityType(
 *   id = "heartbeat_stream_entity",
 *   label = @Translation("Heartbeat stream entity"),
 *   handlers = {
 *     "list_builder" = "Drupal\heartbeat8\HeartbeatStreamEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\heartbeat8\Form\HeartbeatStreamEntityForm",
 *       "edit" = "Drupal\heartbeat8\Form\HeartbeatStreamEntityForm",
 *       "delete" = "Drupal\heartbeat8\Form\HeartbeatStreamEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\heartbeat8\HeartbeatStreamEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "heartbeat_stream_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/heartbeat/heartbeat_stream_entity/{heartbeat_stream_entity}",
 *     "add-form" = "/admin/structure/heartbeat/heartbeat_stream_entity/add",
 *     "edit-form" = "/admin/structure/heartbeat/heartbeat_stream_entity/{heartbeat_stream_entity}/edit",
 *     "delete-form" = "/admin/structure/heartbeat/heartbeat_stream_entity/{heartbeat_stream_entity}/delete",
 *     "collection" = "/admin/structure/heartbeat/heartbeat_stream_entity"
 *   }
 * )
 */
class HeartbeatStreamEntity extends ConfigEntityBase implements HeartbeatStreamEntityInterface {
  /**
   * The Heartbeat stream entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Heartbeat stream entity label.
   *
   * @var string
   */
//  protected $label;



  /*
   * Sets the unique Message ID
   *
   * @param string $messageId
   *  The unique Message ID to represent
   *  all messages of this type
   */


  public function setMessageId($messageId) {
    // TODO: Implement setMessageId() method.
  }


  /*
  * Gets the unique Message ID
  *
  * @return string
  *  The Stream's Message ID
  */

  public function getMessageId() {
    // TODO: Implement getMessageId() method.
  }

  /**
   * Sets the description of the stream
   *
   * @param string $description
   *  Describing streams of this type
   */
  public function setDescription($description) {
    // TODO: Implement setDescription() method.
  }

  /**
   * Gets the description of the stream
   *
   * @return string
   *  The Stream's description
   */
  public function getDescription() {
    // TODO: Implement getDescription() method.
  }

  /**
   * Sets the translatable message
   * This message creates the structure of each message
   *
   * @param string $message
   *  The template message serving as the foundation of each message structure of this stream type
   */
  public function setMessage($message) {
    // TODO: Implement setMessage() method.
  }

  /**
   * Gets the translatable message of the stream
   *
   * @return string
   *  The Stream's message
   */
  public function getMessage() {
    // TODO: Implement getMessage() method.
  }

  /**
   * Sets the translatable concatenated message
   *
   * @param string $messageConcat
   *
   */
  public function setMessageConcat($messageConcat) {
    // TODO: Implement setMessageConcat() method.
  }

  /**
   * Gets the concatenated message of the stream
   *
   * @return string
   *  The Stream's concatenated message
   */
  public function getMessageConcat() {
    // TODO: Implement getMessageConcat() method.
  }

  /**
   * Sets the Permissions for this message stream
   *
   * @param int $perms
   *
   */
  public function setPerms($perms) {
    // TODO: Implement setPerms() method.
  }

  /**
   * Gets the Permissions of this message stream
   *
   * @return int
   *  The stream's permissions
   */
  public function getPerms() {
    // TODO: Implement getPerms() method.
  }

  /**
   * Sets the Group Type for this message stream
   *
   * @param string $groupType
   *
   */
  public function setGroupType($groupType) {
    // TODO: Implement setGroupType() method.
  }

  /**
   * Gets the Group Type of this message stream
   *
   * @return string
   *  The stream's Group Type
   */
  public function getGroupType() {
    // TODO: Implement getGroupType() method.
  }

  /**
   * Sets the arguments for the concatenated message
   *
   * @param string $concatArgs
   *
   */
  public function setConcatArgs($concatArgs) {
    // TODO: Implement setConcatArgs() method.
  }

  /**
   * Gets the arguments for the concatenated message
   *
   * @return string
   *  The stream's arguments for the concatenated message
   */
  public function getConcateArgs() {
    // TODO: Implement getConcateArgs() method.
  }

  /**
   * Sets the variables for this message stream
   *
   * @param string $variables
   *
   */
  public function setVariables($variables) {
    // TODO: Implement setVariables() method.
  }

  /**
   * Gets the variables of this message stream
   *
   * @return string
   *  The stream's variables
   */
  public function getVariables() {
    // TODO: Implement getVariables() method.
  }

  /**
   * Sets the attachments for this message stream
   *
   * @param string $attachments
   *
   */
  public function setAttachments($attachments) {
    // TODO: Implement setAttachments() method.
  }

  /**
   * Gets the attachments of this message stream
   *
   * @return string
   *  The stream's attachments
   */
  public function getAttachments() {
    // TODO: Implement getAttachments() method.
  }

  public function label() {
    return parent::label();
//    == null? "" : parent::label();
  }

  public function id()
  {
    return parent::id();
//    == null ? 69 : parent::id(); // TODO: Change the autogenerated stub
  }
}
