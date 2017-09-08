<?php

namespace Drupal\heartbeat\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'FriendInteractBlock' block.
 *
 * @Block(
 *  id = "friend_interact_block",
 *  admin_label = @Translation("Friend interact block"),
 * )
 */
class FriendInteractBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $build = [
      '#type' => 'markup'

    ];

    return $build;
  }

}
