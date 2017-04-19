<?php

namespace Drupal\heartbeat8\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Heartbeat entities.
 */
class HeartbeatViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['heartbeat']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Heartbeat'),
      'help' => $this->t('The Heartbeat ID.'),
    );

    return $data;
  }

}
