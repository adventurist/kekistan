<?php

namespace Drupal\heartbeat8;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Heartbeat entities.
 *
 * @ingroup heartbeat8
 */
interface HeartbeatInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.
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
   * @return \Drupal\heartbeat8\HeartbeatInterface
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
   * @return \Drupal\heartbeat8\HeartbeatInterface
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
   * @return \Drupal\heartbeat8\HeartbeatInterface
   *   The called Heartbeat entity.
   */
  public function setPublished($published);

}
