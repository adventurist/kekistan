<?php

namespace Drupal\heartbeat8;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Heartbeat stream entity entities.
 */
interface HeartbeatStreamEntityInterface extends ConfigEntityInterface {
  // Add get/set methods for your configuration properties here.


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
   * Gets the description of the stream
   *
   * @return string
   *  The Stream's description
   */
  public function getDescription();


  /**
   * Sets the translatable message
   * This message creates the structure of each message
   *
   * @param string $message
   *  The template message serving as the foundation of each message structure of this stream type
   */

  public function setMessage();

  public function getMessage();

  public function setMessageConcat();

  public function getMessageConcat();

  public function setPerms();

  public function getPerms();

  public function setGroupType();

  public function getGroupType();


}
