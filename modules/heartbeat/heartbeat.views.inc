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
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

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
    // Base data.
    $data = [];
    $data['heartbeat_friendship'] = [];
    $data['heartbeat_friendship']['table'] = [];
    $data['heartbeat_friendship']['table']['group'] = t('Heartbeat Friendship');
    $data['heartbeat_friendship']['table']['base'] = [
        'field' =>  'id',
        'title' => t('Friendships'),
        'help' => t('Maintains friendship status between Users'),
    //        'query_id' => 'teacher',

    ];

    // Fields.

    $data['heartbeat_friendship']['id'] = [
        'title' => t('id'),
        'help' => t('Friendship ID'),
        'field' => [
            'id' => 'numeric',
        ],
        'sort' => array(
            // ID of sort handler plugin to use.
            'id' => 'standard',
        ),

        'filter' => array(
            // ID of filter handler plugin to use.
            'id' => 'numeric',
        ),

        'argument' => array(
            // ID of argument handler plugin to use.
            'id' => 'numeric',
        ),
    ];

    $data['heartbeat_friendship']['uid'] = [
        'title' => t('UID'),
        'help' => t('User\'s Unique ID.'),
        'field' => [
            'id' => 'numeric',
        ],
        'sort' => array(
            // ID of sort handler plugin to use.
            'id' => 'standard',
        ),

        'filter' => array(
            // ID of filter handler plugin to use.
            'id' => 'numeric',
        ),

        'argument' => array(
            // ID of argument handler plugin to use.
            'id' => 'numeric',
        ),
        'relationship' => array(
          'title' => t('User'),
          'help' => t(''),
          'base' => 'users_field_data',
          'base field' => 'uid',
          'id' => 'standard',
        ),

    ];

    $data['heartbeat_friendship']['uid_target'] = [
        'title' => t('UID Target'),
        'help' => t('Unique ID of the User who is the target of the relationship'),
        'field' => [
            'id' => 'numeric',
        ],
        'sort' => array(
            // ID of sort handler plugin to use.
            'id' => 'standard',
        ),

        'filter' => array(
            // ID of filter handler plugin to use.
            'id' => 'numeric',
        ),

        'argument' => array(
            // ID of argument handler plugin to use.
            'id' => 'numeric',
        ),
        'relationship' => array(
          'title' => t('User Target'),
          'help' => t(''),
          'base' => 'users_field_data',
          'base field' => 'uid',
          'id' => 'standard',
        ),

    ];

    $data['heartbeat_friendship']['status'] = [
        'title' => t('Status'),
        'help' => t('The status of the friendship'),
        'field' => [
            'id' => 'numeric',
        ],
        'sort' => array(
            // ID of sort handler plugin to use.
            'id' => 'standard',
        ),

        'filter' => array(
            // ID of filter handler plugin to use.
            'id' => 'numeric',
        ),

        'argument' => array(
            // ID of argument handler plugin to use.
            'id' => 'numeric',
        ),
      ];

  $data['heartbeat_friendship']['table']['join'] = [
    'users_field_data' => [
      'left_field' => 'uid',
      'field' => 'uid',
    ],
  ];



    return $data;
}

/**
 * Implements hook_views_query_alter().
 * @param ViewExecutable $view
 * @param QueryPluginBase $query
 */
function heartbeat_views_query_alter(ViewExecutable $view, QueryPluginBase $query) {


  switch ($view->id()) {
    case 'heartbeat_friendship':


      $configuration = array(
        'table' => 'users_field_data',
        'field' => 'uid',
        'left_table' => 'heartbeat_friendship',
        'left_field' => 'uid_target',
        'operator' => '=',
      );
      $join = Views::pluginManager('join')->createInstance('standard', $configuration);
      $query->addRelationship('users_target', $join);
      break;

    case 'user_friends':

      $friendData = \json_decode(\Drupal::config('heartbeat_friendship.settings')->get('data'));
      $friendUids = [];

      foreach ($friendData as $data) {
        $friendUids[] = $data->uid;
        $friendUids[] = $data->uid_target;
      }

      $query->addWhere('AND', 'users_field_data.uid', \Drupal::currentUser()->id(), '!=');
      $query->addWhere('AND', 'users_field_data.uid', array_unique($friendUids), 'IN');

      break;
  }
}

/**
 * Implements hook_views_pre_render().
 */
//function heartbeat_views_pre_render(ViewExecutable $view) {

//  $friendStatuses = [-1 => 'No Friendship', 0 => 'Pending Friendship', 1 => 'Minimum Bromance'];

//  if ($view->id() === 'heartbeat_friendship') {
//    foreach ($view->result as $row) {
////      $row->heartbeat_friendship_status = $friendStatuses[$row->heartbeat_friendship_status];
//    }
//  }
//}
