<?php

namespace Drupal\heartbeat\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class HeartbeatAutocompleteSubscriber extends RouteSubscriberBase {
  public function alterRoutes(RouteCollection $collection) {

    if ($route = $collection->get('system.entity_autocomplete')) {
      $route->setDefault('_controller', '\Drupal\heartbeat\Controller\HeartbeatAutocompleteController::handleAutocomplete');
    }
  }
}
