<?php

namespace Drupal\heartbeat;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\EntityAutocompleteMatcher;
use Drupal\file\Entity\File;
use Drupal\user\UserInterface;

class HeartbeatAutocompleteMatcher extends EntityAutocompleteMatcher {
  private $entityTypeManager;

  /**
   * Gets matched labels based on a given search string.
   */
  public function getMatches($target_type, $selection_handler, $selection_settings, $string = '') {

    if ($target_type !== 'user') {
      return parent::getMatches($target_type, $selection_handler, $selection_settings, $string);
    }

    $matches = [];

    $friendData = \json_decode(\Drupal::config('heartbeat_friendship.settings')->get('data'));
    $friendUids = [];

    foreach ($friendData as $data) {
      $friendUids[] = $data->uid;
      $friendUids[] = $data->uid_target;
    }


    $options = [
      'target_type'      => $target_type,
      'handler'          => $selection_handler,
      'handler_settings' => $selection_settings,
    ];
    $handler = $this->selectionManager->getInstance($options);
    if (isset($string)) {
      // Get an array of matching entities.
      $match_operator = !empty($selection_settings['match_operator']) ? $selection_settings['match_operator'] : 'CONTAINS';
      $entity_labels = $handler->getReferenceableEntities($string, $match_operator, 10);
      // Loop through the entities and convert them into autocomplete output.
      foreach ($entity_labels as $values) {
        foreach ($values as $entity_id => $label) {
          if (in_array($entity_id, $friendUids)) {
            $entity = \Drupal::entityTypeManager()->getStorage($target_type)->load($entity_id);
            $entity = \Drupal::entityManager()->getTranslationFromContext($entity);
            $type = !empty($entity->type->entity) ? $entity->type->entity->label() : $entity->bundle();
            $status = '';
            if ($entity->getEntityType()->id() == 'node') {
              $status = ($entity->isPublished()) ? ", Published" : ", Unpublished";
            }
            $key = $label . ' (' . $entity_id . ')';
            // Strip things like starting/trailing white spaces, line breaks and tags.
            $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
            // Names containing commas or quotes must be wrapped in quotes.
            $key = Tags::encode($key);
            if ($entity instanceof UserInterface) {
              $pic = $entity->get('user_picture')->getValue();
              if (!empty($pic)) {
                $pic = File::load($pic[0]['target_id']);
                if ($pic !== null) {
                  $style = \Drupal::entityTypeManager()->getStorage('image_style')
                    ->load('thumbnail');
                  $rendered = '<div class="heartbeat-autocomplete-image"><img src="' . $style->buildUrl($pic->getFileUri()) . '" /></div>';
                }
              }
            }

            $label = '<a href="/user/' . $entity->id() . '"><div class="heartbeat-autocomplete-user"> ' . $label . ' (' . $entity_id . ') [' . $type . $status . ']</div>' . $rendered . '</a>';
            $matches[] = ['value' => $key, 'label' => $label];
          }
        }
      }
    }
    return $matches;
  }
}
