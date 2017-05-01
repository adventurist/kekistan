<?php

/**
 * @file
 * Contains heartbeat_stream.page.inc.
 *
 * Page callback for Heartbeat stream entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Heartbeat stream templates.
 *
 * Default template: heartbeat_stream.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_heartbeat_stream(array &$variables) {
  // Fetch HeartbeatStream Entity Object.
  $heartbeat_stream = $variables['elements']['#heartbeat_stream'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
