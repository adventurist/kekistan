<?php

namespace Drupal\heartbeat\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Heartbeat entities.
 */
class HeartbeatViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
