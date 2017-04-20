<?php

namespace Drupal\heartbeat8\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Heartbeat entities.
 *
 * @ingroup heartbeat8
 */
interface HeartbeatInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Heartbeat type.
   *
   * @return string
   *   The Heartbeat type.
   */
  public function getType();

  /**
   * Gets the Heartbeat name.
   *
   * @return string
   *   Name of the Heartbeat.
   */
  public function getName();

  /**
   * Sets the Heartbeat name.
   *
   * @param string $name
   *   The Heartbeat name.
   *
   * @return \Drupal\heartbeat8\Entity\HeartbeatInterface
   *   The called Heartbeat entity.
   */
  public function setName($name);

  /**
   * Gets the Heartbeat creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Heartbeat.
   */
  public function getCreatedTime();

  /**
   * Sets the Heartbeat creation timestamp.
   *
   * @param int $timestamp
   *   The Heartbeat creation timestamp.
   *
   * @return \Drupal\heartbeat8\Entity\HeartbeatInterface
   *   The called Heartbeat entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Heartbeat published status indicator.
   *
   * Unpublished Heartbeat are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Heartbeat is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Heartbeat.
   *
   * @param bool $published
   *   TRUE to set this Heartbeat to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\heartbeat8\Entity\HeartbeatInterface
   *   The called Heartbeat entity.
   */
  public function setPublished($published);

  /**
   * Gets the Heartbeat revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Heartbeat revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\heartbeat8\Entity\HeartbeatInterface
   *   The called Heartbeat entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Heartbeat revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Heartbeat revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\heartbeat8\Entity\HeartbeatInterface
   *   The called Heartbeat entity.
   */
  public function setRevisionUserId($uid);

}
