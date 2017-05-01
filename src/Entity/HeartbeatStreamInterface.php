<?php

namespace Drupal\heartbeat8\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Heartbeat stream entities.
 *
 * @ingroup heartbeat8
 */
interface HeartbeatStreamInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Heartbeat stream name.
   *
   * @return string
   *   Name of the Heartbeat stream.
   */
  public function getName();

  /**
   * Sets the Heartbeat stream name.
   *
   * @param string $name
   *   The Heartbeat stream name.
   *
   * @return \Drupal\heartbeat8\Entity\HeartbeatStreamInterface
   *   The called Heartbeat stream entity.
   */
  public function setName($name);

  /**
   * Gets the Heartbeat stream creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Heartbeat stream.
   */
  public function getCreatedTime();

  /**
   * Sets the Heartbeat stream creation timestamp.
   *
   * @param int $timestamp
   *   The Heartbeat stream creation timestamp.
   *
   * @return \Drupal\heartbeat8\Entity\HeartbeatStreamInterface
   *   The called Heartbeat stream entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Heartbeat stream published status indicator.
   *
   * Unpublished Heartbeat stream are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Heartbeat stream is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Heartbeat stream.
   *
   * @param bool $published
   *   TRUE to set this Heartbeat stream to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\heartbeat8\Entity\HeartbeatStreamInterface
   *   The called Heartbeat stream entity.
   */
  public function setPublished($published);

  /**
   * Gets the Heartbeat stream revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Heartbeat stream revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\heartbeat8\Entity\HeartbeatStreamInterface
   *   The called Heartbeat stream entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Heartbeat stream revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Heartbeat stream revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\heartbeat8\Entity\HeartbeatStreamInterface
   *   The called Heartbeat stream entity.
   */
  public function setRevisionUserId($uid);

}
