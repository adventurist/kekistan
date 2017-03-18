<?php

namespace Drupal\heartbeat8\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Heartbeat type entity.
 *
 * @ConfigEntityType(
 *   id = "heartbeat_type",
 *   label = @Translation("Heartbeat type"),
 *   handlers = {
 *     "list_builder" = "Drupal\heartbeat8\HeartbeatTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\heartbeat8\Form\HeartbeatTypeForm",
 *       "edit" = "Drupal\heartbeat8\Form\HeartbeatTypeForm",
 *       "delete" = "Drupal\heartbeat8\Form\HeartbeatTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\heartbeat8\HeartbeatTypeHtmlRouteProvider",
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
   * The Heartbeat type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Heartbeat type label.
   *
   * @var string
   */
  protected $label;

}
