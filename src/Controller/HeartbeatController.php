<?php

namespace Drupal\heartbeat8\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\heartbeat8\HeartbeatStreamConfig;

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


  public function page() {
    return array(
      '#type' => 'markup',
      '#markup' => l(
        t('Link'),
        'heartbeat/modaltest/nojs',
        array(
          'attributes' => array(
            'class' => 'use-ajax',
          ),
        )
      ),
      '#attached' => array(
        'library' => array(
          array('system', 'drupal.ajax'),
        ),
      ),
    );
  }

  public function openModal($js = 'nojs') {

    $options = $js == 'ajax' ? array(
      'width' => '80%',
      ) : array();

    $response = new AjaxResponse();

    $response->addCommand(new OpenModalDialogCommand(t('Modal'), t('This is the Heartbeat Modal Dialog with AJAX, yo'), $options));

    return $response;
  }

}
