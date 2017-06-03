<?php

namespace Drupal\heartbeat\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Heartbeat type entities.
 */
interface HeartbeatTypeInterface extends ConfigEntityInterface {



  /*
   * Sets the unique Message ID
   *
   * @param string $messageId
   *  The unique Message ID to represent
   *  all messages of this type
   */

  public function setMessageId($messageId);

  /*
   * Gets the unique Message ID
   *
   * @return string
   *  The Stream's Message ID
   */

  public function getMessageId();


  /**
   * Sets the description of the stream
   *
   * @param string $description
   *  Describing streams of this type
   */

  public function setDescription($description);

  /**
   * Gets the description of the HeartbeatType
   *
   * @return string
   *  The Stream's description
   */

  public function getDescription();

  /**
   * Sets the main Entity Type of the Heartbeat Type
   *
   * @param string $mainentity
   *  Describing entity type used in this Heartbeat Type
   */

  public function setMainEntity($mainentity);

  /**
   * Gets the main Entity Type of the Heartbeat Type
   *
   * @return string
   *  The Heartbeat Type's main Entity Type
   */

  public function getMainEntity();

  /**
   * Sets the translatable message
   * This message creates the structure of each message
   *
   * @param string $message
   *  The template message serving as the foundation of each message structure of this stream type
   */


  public function setMessage($message);

  /**
   * Gets the translatable message of the stream
   *
   * @return string
   *  The Stream's message
   */

  public function getMessage();


//  /**
//   * Sets the translatable concatenated message
//   *
//   * @param string $messageConcat
//   *
//   */
//
//  public function setMessageConcat($messageConcat);
//
//
//  /**
//   * Gets the concatenated message of the stream
//   *
//   * @return string
//   *  The Stream's concatenated message
//   */
//
//  public function getMessageConcat();


  /**
   * Sets the Permissions for this message stream
   *
   * @param int $perms
   *
   */

  public function setPerms($perms);


  /**
   * Gets the Permissions of this message stream
   *
   * @return int
   *  The stream's permissions
   */

  public function getPerms();


  /**
   * Sets the Group Type for this message stream
   *
   * @param string $groupType
   *
   */

  public function setGroupType($groupType);


  /**
   * Gets the Group Type of this message stream
   *
   * @return string
   *  The stream's Group Type
   */

  public function getGroupType();


  /**
   * Sets the arguments for the concatenated message
   *
   * @param string $arguments
   *
   */


  public function setArguments($arguments);


  /**
   * Gets the arguments for the concatenated message
   *
   * @return string
   *  The stream's arguments for the concatenated message
   */

  public function getArguments();



  /**
   * Sets the variables for this message stream
   *
   * @param string $variables
   *
   */

  public function setVariables($variables);


  /**
   * Gets the variables of this message stream
   *
   * @return string
   *  The stream's variables
   */

  public function getVariables();


  /**
   * Sets the bundle targeted for this Heartbeat type
   *
   * @param string $variables
   *
   */

  public function setBundle($bundle);


  /**
   * Gets the bundle of this Heartbeat type
   *
   * @return string
   *  The stream's variables
   */

  public function getBundle();


  /**
   * @return mixed
   */
  public function setWeight($weight);

  /**
   * @param $weight
   * @return mixed
   */
  public function getWeight();

//  /**
//   * Sets the attachments for this message stream
//   *
//   * @param string $attachments
//   *
//   */
//
//  public function setAttachments($attachments);
//
//
//  /**
//   * Gets the attachments of this message stream
//   *
//   * @return string
//   *  The stream's attachments
//   */
//
//  public function getAttachments();

}
