<?php

use Drupal\Core\Database\Query\SelectExtender;
/**
 * Created by IntelliJ IDEA.
 * User: logicp
 * Date: 5/28/17
 * Time: 1:41 PM
 */

class HeartbeatSelectExtender extends SelectExtender {


  public $lastActivityId = 0;


  /**
   * Sets the last id
   */
  public function setLastActivityId($lastActivityId) {
    $this->lastActivityId = $lastActivityId;
    $this->query->condition('h.id', $this->lastActivityId, '>');
  }

  /**
   * Sets the offset timestamps.
   */
  public function setOffsetTime($before, $after = 0) {
    $this->query->condition('ha.timestamp', $before, '<');

    if ($after > 0) {
      $this->query->condition('ha.timestamp', $_SERVER['REQUEST_TIME'] - $after, '>');
    }
  }


}
