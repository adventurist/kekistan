<?php

namespace Drupal\heartbeat8\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Heartbeat stream entities.
 */
interface HeartbeatStreamInterface extends ConfigEntityInterface {

  /**
   * @return mixed
   */

  public function getName();

  /**
   * @param mixed $name
   */

  public function setName($name);

  /**
   * @return mixed
   */

  public function getClass();

  /**
   * @param mixed $class
   */

  public function setClass($class);

  /**
   * @return mixed
   */

  public function getRealClass();

  /**
   * @param mixed $real_class
   */

  public function setRealClass($real_class);

  /**
   * @return mixed
   */

  public function getPath();

  /**
   * @param mixed $path
   */

  public function setPath($path);

  /**
   * @return mixed
   */

  public function getTitle();

  /**
   * @param mixed $title
   */

  public function setTitle($title);

  /**
   * @return mixed
   */

  public function getModule();

  /**
   * @param mixed $module
   */

  public function setModule($module);

  /**
   * @return mixed
   */

  public function getVariables();

  /**
   * @param mixed $variables
   */

  public function setVariables($variables);

  /**
   * @return mixed
   */

  public function getSettings();

  /**
   * @param mixed $settings
   */

  public function setSettings($settings);

}
