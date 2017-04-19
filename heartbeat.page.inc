<?php

/**
 * @file
 * Contains heartbeat.page.inc.
 *
 * Page callback for Heartbeat entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

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
  $heartbeat = $variables['elements']['#heartbeat'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
