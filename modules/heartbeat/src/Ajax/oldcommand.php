<?php

namespace Drupal\heartbeat;

use Drupal\Core\Ajax\CommandInterface;

class SelectFeedCommandss implements CommandInterface {
    protected $message;
    // Constructs a ReadMessageCommand object.
    public function __construct($message) {
        $this->message = $message;
    }
    // Implements Drupal\Core\Ajax\CommandInterface:render().
    public function render() {
        return array(
            'command' => 'selectFeed',
            'feed' => $this->message
        );
    }

    public static function hello() {
        return 'jigga';
}
}
