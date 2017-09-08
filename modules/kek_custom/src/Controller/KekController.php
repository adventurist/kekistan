<?php

namespace Drupal\kek_custom\Controller;

use Drupal\block\BlockViewBuilder;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class KekController.
 *
 * @package Drupal\kek_custom\Controller
 */
class KekController extends ControllerBase {
  /**
   * Loginblock.
   *
   * @return string
   *   Return Hello string.
   */
  public function loginBlock() {
    return BlockViewBuilder::lazyBuilder('kekloginblock', 'full');
  }

}
