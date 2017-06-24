<?php

namespace Drupal\heartbeat\Ajax;

/**
 * Created by IntelliJ IDEA.
 * User: logicp
 * Date: 5/28/17
 * Time: 11:38 PM
 */

use Drupal\Core\Ajax\CommandInterface;

class ClearPreviewCommand implements CommandInterface {
    protected $message;

    public function __construct($message) {
        $this->message = $message;
    }

    public function render() {

        return array(
            'command' => 'clearPreview',
            'clear' => $this->message,
        );
    }
}
