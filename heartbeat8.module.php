<?php
/**
 * Created by IntelliJ IDEA.
 * User: logicp
 * Date: 4/5/17
 * Time: 1:18 AM
 */
use Drupal\Component\Utility\Xss;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Query\AlterableInterface;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\StatementInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\language\ConfigurableLanguageInterface;
use Drupal\Heartbeat8\Entity\Heartbeat;
use Drupal\Heartbeat8\Entity\HeartbeatType;
use Drupal\Heartbeat8\Entity\HeartbeatInterface;
use Drupal\Heartbeat8\Entity\HeartbeatTypeInterface;


function heartbeat8_cron() {

}
