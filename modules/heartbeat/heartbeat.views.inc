<?php

/**
 * @file
 * Contains heartbeat\heartbeat.views.inc..
 * Provide a custom views field data that isn't tied to any other module. */

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Render\Markup;
use Drupal\field\FieldConfigInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\system\ActionConfigEntityInterface;

/**
* Implements hook_views_data().
*/
function heartbeat_views_data() {

    $data['views']['table']['group'] = t('Custom Global');
    $data['views']['table']['join'] = array(
      // #global is a special flag which allows a table to appear all the time.
      '#global' => array(),
    );


    $data['views']['heartbeat_message_field'] = array(
        'title' => t('Heartbeat message field'),
        'help' => t('Heartbeat field formatter which allows for rendering of HTML'),
        'field' => array(
            'id' => 'heartbeat_message_field',
        ),
    );

    return $data;
}
