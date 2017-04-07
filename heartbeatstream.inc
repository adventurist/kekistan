<?php

/**
 * @file
 *  HeartbeatStream object takes configuration parameters to create a stream of activity objects.
 *  pre-query, query and post-query phases of the stream can be specified and manipulated here
 */


/**
 * Abstract Class HeartbeatStream
 *  Base class with template methods. HeartbeatStream is a state object given to the HeartbeatStreamBuilder
 * to set access on current request
 */
abstract class HeartbeatStream
{


  // Query object for this stream.
  protected $query = NULL;

  // Configuration object for this stream.
  public $config = NULL;

  // Well-formed activity messages.
  public $messages = array();

  // String prefix on top of the stream.
  public $prefix = '';

  // String suffix under a stream.
  public $suffix = '';

  // Templates available to show.
  public $templates = array();

  // Contextual arguments.
  public $contextual_arguments = array();

  // Denied templates.
  protected $templates_denied = array();

  // Language at display time.
  protected $language = LANGUAGE_NONE;

  // exclude Og.
  protected $exclude_og = FALSE;

  // The stream owner or activity watcher.
  protected $_uid = 0;

  // Array of runtime notices, warnings and errors.
  protected $_errors = array();

  // Indicates if there are runtime errors.
  protected $_has_errors = FALSE;

  // Indicates whether the page has modal requirement.
  protected $needsModal = TRUE;

  // Time where activity starts.
  protected $_offset_time = 0;

  // Maximum time where activity must end.
  protected $oldest_date = 604800;

  // Maximum number of activity messages to show.
  protected $messages_max = 0;

  // Latest user activity id fetched.
  protected $latest_activity_id = 0;

  // Indicates if the stream is displayed on a page or not.
  protected $_page = FALSE;

  // Indicates if this is an ajax request.
  protected $ajax = 0;

  // Can page means if we can show more messages
  protected $canPage = FALSE;

  // User view type of the stream instance.
  protected $_whoisuser_type = self::VIEWER;

  // The user who is viewing the activity stream.
  protected $viewer = null;

  // The user who's activity stream is viewed.
  protected $viewed = null;

  // View mode to display message.
  protected $view_mode = 'default';

  protected $_whoisuser_types = array(
    self::VIEWER => 'Viewing user',
    self::VIEWED => 'Viewed user'
  );

  // User viewer types.
  const VIEWER = 0;
  const VIEWED = 1;


  final public function __construct(HeartbeatStreamConfig $streamConfig, $page = FALSE, $account = NULL) {

    $this->_page = $page;
    $this->setConfig($streamConfig);
    $this->setAjax();

    if (empty($this->_offset_time)) {
      $this->setOffsetTime();
    }

    $this->setViewer(\Drupal::currentUser());
    $this->setViewed($account);

    $this->setAvailableTemplates();

    $this->construct();

    $this->setContextualArguments();

  }

  /**
   * Fake Constructor Method
   */

  public function construct() {

  }

  /**
   *
   */
  protected function setContextualArguments() {
    $contextualArguments = \Drupal::request()->query->get('contextualArguments');
    if (!empty($contextualArguments) && isset($contextualArguments['uid_target'])) {
      $this->contextual_arguments = $contextualArguments['uid_target'];
    } elseif ($this->viewed->uid != $this->viewer->uid) {
      $this->contextual_arguments['uid_target'] = $this->viewed->uid;
    }

    //TODO Figure out a way to attach $this->contextual_arguments to the drupalSettings object with an id of "heartbeatContextualArguments"
    //see below for implementation in a page callback or form alter
    //    $variables['#attached']['drupalSettings']['heartbeatContextualArguments'] = $this->contextual_arguments;

  }
}
