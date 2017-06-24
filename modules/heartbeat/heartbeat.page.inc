<?php

/**
 * @file
 * Contains heartbeat.page.inc.
 *
 * Page callback for Heartbeat entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Heartbeat templates.
 *
 * Default template: heartbeat.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_heartbeat(array &$variables) {
  // Fetch Heartbeat Entity Object.
  $entity = $variables['elements']['#heartbeat'];

  if ($entity->getEntityType() == 'heartbeat' && $entity->isPublished()) {
    $node = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->load($entity->getNid()->getValue()[0]['target_id']);

    if ($node !== null) {
      $renderedNode = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, 'full');
      $variables['content']['node'] = $renderedNode;
    }
  }
    // Helpful $content variable for templates.
    foreach (Element::children($variables['elements']) as $key) {
      $variables['content'][$key] = $variables['elements'][$key];
    }

}
