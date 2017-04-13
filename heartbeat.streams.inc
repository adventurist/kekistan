<?php

use \Drupal\Core\Extension\ModuleHandler;

/**
 * Prepares a heartbeat stream
 * @param $stream_name the name of the stream
 */

function heartbeat_stream($stream_name, $page = 0, $account = NULL) {
  static $streams;

  try {

    if (!isset($streams[$stream_name])) {
      //Load configuration object for the stream
      $streamConfig = heartbeat_stream_config_load($stream_name);

      //Load the stream if access is permitted
      if (_heartbeat_stream_has_access($streamConfig)) {

        $heartbeatStream = HeartbeatStreamFactory::getStream($streamConfig, $page, $account);

        //Add settings that need to be set for each stream
        //TODO equivalent drupal_add_js to attach streamconfig messages to the drupalSettings object
        /*
         * drupal_add_js(array('heartbeatPollNewerMessages' => array($streamConfig->class => $streamConfig->poll_messages)), 'setting');
         * drupal_add_js(array('heartbeatPollTypes' => array($streamConfig->class => $streamConfig->poll_messages_type)), 'setting');
         *
         *
         */

        //Give other modules a chance to take action when stream is loaded

        ModuleHandler::invokeAll('heartbeat_stream_load', $heartbeatStream);

        if ($heartbeatStream instanceof HeartbeatStream && !$heartbeatStream->hasErrors()) {
          $streams[$stream_name] = $heartbeatStream;
        } else {
          $streams[$stream_name] = FALSE;
        }
      } else {
        $streams[$stream_name] = FALSE;
      }
    }
  } catch (HeartbeatInvalidStreamException $he) {
    $errorMessage = $he->getMessage();
    drupal_set_message($errorMessage);
    \Drupal::logger('HeartbeatDEBUG')->error('Error message: %errMsg', array(
      '%errMsg' => $errorMessage
    ));
  }

  return $streams[$stream_name];
}

/*
 * Builds a heartbeat stream in the StreamObject
 */

function heartbeat_stream_build(HeartbeatStream &$heartbeatStream) {
  //Load messages
  $messages = $heartbeatStream->execute();
}

/*
 * Builds a stream from Views Results
 */

function heartbeat_stream_views_build(HeartbeatStream &$heartbeatStream, $view_mode = NULL) {

  if (isset($view_mode)) {
    $heartbeatStream->setViewMode($view_mode);
  }

  $build = $heartbeatStream->render();

  return $build;
}


/**
 * Load all the stream configuration objects
 * @param bool $reset
 *  Indicates whether the stream data needs to be rebuilt
 */

function heartbeat_stream_config_load_all($reset = FALSE) {
  $streams = &drupal_static('heartbeat_streams');

  if (!$reset && $object = cache_get('heartbeat_streams')) {
    $streams = $object->data;
  } else {
    //TODO fetch streams with alternative to ctools export
  }
}

