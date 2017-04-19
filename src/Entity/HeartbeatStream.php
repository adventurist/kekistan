<?php

namespace Drupal\heartbeat8\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\heartbeat8\HeartbeatStreamInterface;

/**
 * Defines the Heartbeat Stream entity.
 *
 * @ConfigEntityType(
 *   id = "heartbeat_stream",
 *   label = @Translation("Heartbeat Stream"),
 *   handlers = {
 *     "list_builder" = "Drupal\heartbeat8\HeartbeatStreamListBuilder",
 *     "form" = {
 *       "add" = "Drupal\heartbeat8\Form\HeartbeatStreamForm",
 *       "edit" = "Drupal\heartbeat8\Form\HeartbeatStreamForm",
 *       "delete" = "Drupal\heartbeat8\Form\HeartbeatStreamDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\heartbeat8\HeartbeatStreamHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "heartbeat_stream",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/heartbeat/heartbeat_stream/{heartbeat_stream}",
 *     "add-form" = "/admin/structure/heartbeat/heartbeat_stream/add",
 *     "edit-form" = "/admin/structure/heartbeat/heartbeat_stream/{heartbeat_stream}/edit",
 *     "delete-form" = "/admin/structure/heartbeat/heartbeat_stream/{heartbeat_stream}/delete",
 *     "collection" = "/admin/structure/heartbeat/heartbeat_stream"
 *   }
 * )
 */
class HeartbeatStream extends ConfigEntityBase implements HeartbeatStreamInterface {
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
  protected $concatArgs;
  protected $message;
  protected $variables;
  protected $attachments;
  protected $groupType;
  /**
   * The Heartbeat Stream label.
   *
   * @var string
   */
  protected $label;

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
    $this->message = $message;
  }

  /**
   * Gets the translatable message of the stream
   *
   * @return string
   *  The Stream's message
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * Sets the translatable concatenated message
   *
   * @param string $messageConcat
   *
   */
  public function setMessageConcat($messageConcat) {
    $this->messageConcat = $messageConcat;
  }

  /**
   * Gets the concatenated message of the stream
   *
   * @return string
   *  The Stream's concatenated message
   */
  public function getMessageConcat() {
    return $this->messageConcat;
  }

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
   * @param string $concatArgs
   *
   */
  public function setConcatArgs($concatArgs) {
    $this->concatArgs = $concatArgs;
  }

  /**
   * Gets the arguments for the concatenated message
   *
   * @return string
   *  The stream's arguments for the concatenated message
   */
  public function getConcateArgs() {
    return $this->concatArgs;
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
   * Sets the attachments for this message stream
   *
   * @param string $attachments
   *
   */
  public function setAttachments($attachments) {
    $this->attachments = $attachments;
  }

  /**
   * Gets the attachments of this message stream
   *
   * @return string
   *  The stream's attachments
   */
  public function getAttachments() {
    return $this->attachments;
  }
}
