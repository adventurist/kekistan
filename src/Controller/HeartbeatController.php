<?php

namespace Drupal\heartbeat8\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class HeartbeatController.
 *
 * @package Drupal\heartbeat8\Controller
 */
class HeartbeatController extends ControllerBase {

  /**
   * Confirm.
   *
   * @return string
   *   Return Hello string.
   */
  public function confirm($id) {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: confirm with parameter(s): $id'),
    ];
  }


  public function revisionOverview() {
    return 0;
  }

  public function revisionShow() {
    return 0;
  }
}
