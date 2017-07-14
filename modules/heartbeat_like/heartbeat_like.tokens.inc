<?php

/**
 * @file
 * Token for heartbeat_like.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function heartbeat_like_token_info() {

  $types = array();
  $tokens = array();

  // Flag tokens.
  $types['flagcount'] = array(
    'name' => t('Flags count'),
    'description' => t('Tokens related to flag count (heartbeat_like) module.'),
  );
  $tokens['flagcount']['count'] = array(
    'name' => t('Count'),
    'description' => t('Number of times the flag has been flagged.'),
    'needs-data' => 'flag',
  );

  return array(
    'types' => $types,
    'tokens' => $tokens,
  );
}

 /**
  * Implements hook_tokens().
  */
function heartbeat_like_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = array();

  if ($type == 'flagcount') {
    foreach ($tokens as $name => $original) {
      // Find the desired token by name
      switch ($name) {
        case 'count':

          if (isset($data['flag_id']) && isset($data['entity_id'])) {

            // Query the db for the count associated with this entity.
            $query = \Drupal::database()->select('flag_counts', 'fc');
            $query->fields('fc', ['count']);
            $query->condition('fc.flag_id', $data['flag_id']);
            $query->condition('fc.entity_id', $data['entity_id']);
            $count_db = $query->execute()->fetchAssoc();

            if (!isset($count_db['count'])) {
//              unset ($replacements[$original]);
              $replacements[$original] = "99";
              continue 2;
            } else {

              $replacements[$original] = $count_db['count'];
            }
          }

          break;
      }
    }
  }

  // Return the replacements.
  return $replacements;
}

/**
 * Implements hook_tokens_alter().
 * @param array $replacements
 * @param array $context
 * @param BubbleableMetadata $bubbleable_metadata
 */
function heartbeat_like_tokens_alter(array &$replacements, array $context, \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata) {

  foreach ($replacements as $key => $replacement) {
    if ($key === '[flagcount:count]' && $replacement == 0) {
      $replacements[$key] = "";
    }
  }
}
