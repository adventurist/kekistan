<?php

namespace Drupal\kek_feedfilter\Controller;

use Drupal\Core\Block\BlockManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Renderer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class KekFilterController.
 */
class KekFilterController extends ControllerBase {


  private $renderer;
  private $blockManager;

  /**
   * {@inheritdoc}
   */

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('plugin.manager.block')
    );
  }


  /**
   * KekFilterController constructor.
   * @param Renderer $renderer
   * @param BlockManager $block_manager
   */

  public function __construct(Renderer $renderer, BlockManager $block_manager) {
    $this->renderer = $renderer;
    $this->blockManager = $block_manager;
  }


  /**
   * Getfilterblock.
   *
   *   Return Hello string.
   * @throws \Exception
   */
  public function getFilterBlock() {
    $block = $this->blockManager->createInstance('kek_filter_block')->build();
    return $this->renderer->render($block);
  }

}
