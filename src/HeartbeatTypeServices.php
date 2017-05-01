<?php

namespace Drupal\heartbeat8;
use Drupal\Core\Entity\Query\QueryFactory;

/**
 * Class HeartbeatTypeServices.
 *
 * @package Drupal\heartbeat8
 */
class HeartbeatTypeServices {

  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;
  /**
   * Constructor.
   */
  public function __construct(QueryFactory $entity_query) {
    $this->entityQuery = $entity_query;
  }


  public function getTypes() {
    return $this->entityQuery->get('heartbeat_type')->execute();
  }
}
