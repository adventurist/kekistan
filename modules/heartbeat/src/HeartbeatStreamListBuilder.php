<?php

namespace Drupal\heartbeat8;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Heartbeat stream entities.
 *
 * @ingroup heartbeat8
 */
class HeartbeatStreamListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Heartbeat stream ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\heartbeat8\Entity\HeartbeatStream */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.heartbeat_stream.edit_form', array(
          'heartbeat_stream' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
