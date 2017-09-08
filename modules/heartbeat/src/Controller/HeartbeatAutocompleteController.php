<?php

namespace Drupal\heartbeat\Controller;

use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\heartbeat\HeartbeatAutocompleteMatcher;
use Drupal\system\Controller\EntityAutocompleteController;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HeartbeatAutocompleteController extends EntityAutocompleteController {
  /**
   * The autocomplete matcher for entity references.
   */
  protected $matcher;
  /**
   * {@inheritdoc}
   */
  public function __construct(HeartbeatAutocompleteMatcher $matcher, KeyValueStoreInterface $key_value) {
    $this->matcher = $matcher;
    $this->keyValue = $key_value;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('heartbeat_autocomplete.autocomplete_matcher'),
      $container->get('keyvalue')->get('entity_autocomplete')
    );
  }
}
