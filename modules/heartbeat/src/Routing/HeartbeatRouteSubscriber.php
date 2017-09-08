<?php
/**
 * Created by IntelliJ IDEA.
 * User: logicp
 * Date: 7/1/17
 * Time: 2:00 AM
 */

namespace Drupal\heartbeat\Routing;


use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class HeartbeatRouteSubscriber extends RouteSubscriberBase {


  /**
   * Alters existing routes for a specific collection.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   */
  protected function alterRoutes(RouteCollection $collection) {
    $route = $collection->get('heartbeat.user_edit');

    if ($route !== null) {
      $route->setPath('/user/' . \Drupal::currentUser()->id() . '/edit');
    }
  }
}
