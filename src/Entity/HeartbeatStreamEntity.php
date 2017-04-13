<?php

namespace Drupal\heartbeat8\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\heartbeat8\HeartbeatStreamEntityInterface;

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
  protected $label;

}
