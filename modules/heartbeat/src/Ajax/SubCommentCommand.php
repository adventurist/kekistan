<?php
namespace Drupal\heartbeat\Ajax;
/**
 * Created by IntelliJ IDEA.
 * User: logicp
 * Date: 5/28/17
 * Time: 11:38 PM
 */

use Drupal\Core\Ajax\CommandInterface;

class SubCommentCommand implements CommandInterface {
    protected $cid;

    public function __construct($cid) {
        $this->cid = $cid;
    }

    public function render() {
$jiggajiggawhat = 'null';
$stophere = 'please;';

        return array(
            'command' => 'myfavouritemethodintheworld',

            'cid' => $this->cid
        );
    }
}
