<?php

namespace Drupal\heartbeat8\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a 'Heartbeat' action.
 *
 * @Action(
 *  id = "heartbeat",
 *  label = @Translation("Heartbeat"),
 *  type = "user",
 * )
 */
class Heartbeat extends ActionBase {
  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    // Insert code here.
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\user\UserInterface $object */
    $access = $object->status->access('edit', $account, TRUE)
      ->andIf($object->access('update', $account, TRUE));

    return $return_as_object ? $access : $access->isAllowed();
  }

}
