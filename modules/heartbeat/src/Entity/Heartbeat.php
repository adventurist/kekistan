<?php

namespace Drupal\heartbeat\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Utility\Token;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Database\Database;
use Drupal\user\UserInterface;

/**
 * Defines the Heartbeat entity.
 *
 * @ingroup heartbeat
 *
 * @ContentEntityType(
 *   id = "heartbeat",
 *   label = @Translation("Heartbeat"),
 *   bundle_label = @Translation("Heartbeat type"),
 *   handlers = {
 *     "storage" = "Drupal\heartbeat\HeartbeatStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\heartbeat\HeartbeatListBuilder",
 *     "views_data" = "Drupal\heartbeat\Entity\HeartbeatViewsData",
 *     "translation" = "Drupal\heartbeat\HeartbeatTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\heartbeat\Form\HeartbeatForm",
 *       "add" = "Drupal\heartbeat\Form\HeartbeatForm",
 *       "edit" = "Drupal\heartbeat\Form\HeartbeatForm",
 *       "delete" = "Drupal\heartbeat\Form\HeartbeatDeleteForm",
 *     },
 *     "access" = "Drupal\heartbeat\HeartbeatAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\heartbeat\HeartbeatHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "heartbeat",
 *   data_table = "heartbeat_field_data",
 *   revision_table = "heartbeat_revision",
 *   revision_data_table = "heartbeat_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer heartbeat entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "nid" = "nid",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/heartbeat/{heartbeat}",
 *     "add-page" = "/admin/structure/heartbeat/add",
 *     "add-form" = "/admin/structure/heartbeat/add/{heartbeat_type}",
 *     "edit-form" = "/admin/structure/heartbeat/{heartbeat}/edit",
 *     "delete-form" = "/admin/structure/heartbeat/{heartbeat}/delete",
 *     "version-history" = "/admin/structure/heartbeat/{heartbeat}/revisions",
 *     "revision" = "/admin/structure/heartbeat/{heartbeat}/revisions/{heartbeat_revision}/view",
 *     "revision_revert" = "/admin/structure/heartbeat/{heartbeat}/revisions/{heartbeat_revision}/revert",
 *     "translation_revert" = "/admin/structure/heartbeat/{heartbeat}/revisions/{heartbeat_revision}/revert/{langcode}",
 *     "revision_delete" = "/admin/structure/heartbeat/{heartbeat}/revisions/{heartbeat_revision}/delete",
 *     "collection" = "/admin/structure/heartbeat",
 *   },
 *   bundle_entity_type = "heartbeat_type",
 *   field_ui_base_route = "entity.heartbeat_type.edit_form"
 * )
 */

// Always block from display
const HEARTBEAT_NONE = -1;

// Display only activity messages that are mine or addressed to me
const HEARTBEAT_PRIVATE = 0;

// Only the person that is chosen by the actor, can see the message
const HEARTBEAT_PUBLIC_TO_ADDRESSEE = 1;

// Display activity message of all my user relations, described in contributed modules
const HEARTBEAT_PUBLIC_TO_CONNECTED = 2;

// Everyone can see this activity message, unless this type of message is set to private
const HEARTBEAT_PUBLIC_TO_ALL = 4;


//Group Types

const HEARTBEAT_GROUP_NONE = 11;
const HEARTBEAT_GROUP_SINGLE = 12;
const HEARTBEAT_GROUP_SUMMARY = 13;

const FILE_FIELD = 'Drupal\file\Plugin\Field\FieldType\FileFieldItemList';



class Heartbeat extends RevisionableContentEntityBase implements HeartbeatInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the heartbeat owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * Gets the Heartbeat message.
   *
   * @return string
   *   Message of the Heartbeat.
   */
  public function getMessage() {
    return $this->get('message');
  }

  /**
   * Sets the Heartbeat Message.
   *
   * @param $name
   * @return
   * @internal param string $message The Heartbeat Message
   */
  public function setMessage($message) {
    $this->set('message', $message);
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionCreationTime() {
    return $this->get('revision_timestamp')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionCreationTime($timestamp) {
    $this->set('revision_timestamp', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionUser() {
    return $this->get('revision_uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionUserId($uid) {
    $this->set('revision_uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Heartbeat entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['nid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Node'))
      ->setDescription(t('The content associated with this Heartbeat'))
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default')
      ->setRevisionable(TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Heartbeat entity.'))
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);


    $fields['message'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Message'))
      ->setDescription(t('The message of the Heartbeat entity.'))
      ->setRevisionable(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Heartbeat is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_timestamp'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Revision timestamp'))
      ->setDescription(t('The time that the current revision was created.'))
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Revision user ID'))
      ->setDescription(t('The user ID of the author of the current revision.'))
      ->setSetting('target_type', 'user')
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * Returns the node type label for the passed node.
   *
   * @param \Drupal\heartbeat\Entity\HeartbeatInterface $heartbeat
   *   A heartbeat entity to return the heartbeat type's label for.
   *
   * @return string|false
   *   The heartbeat type label or FALSE if the heartbeat type is not found.
   *
   * @todo Add this as generic helper method for config entities representing
   *   entity bundles.
   */
  public function heartbeat_get_type(HeartbeatInterface $heartbeat) {
    $type = HeartbeatType::load($heartbeat->bundle());
    return $type ? $type->label() : FALSE;
  }


  /**
   * Updates all heartbeat activities of one type to be of another type.
   *
   * @param string $old_id
   *   The current heartbeat type of the activities.
   * @param string $new_id
   *   The new heartbeat type of the activities.
   *
   * @return
   *   The number of activities whose heartbeat type field was modified.
   */
  public function heartbeat_type_update_nodes($old_id, $new_id) {
    return \Drupal::entityManager()->getStorage('heartbeat')->updateType($old_id, $new_id);
  }


  /**
   * Builds a message template for a given HeartbeatType
   *
   * @param HeartbeatType $heartbeatType
   * @param null $mediaData
   * @return null|string
   */
  public static function buildMessage(Token $tokenService, $preparsedMessage, $entities = NULL, $entityType, $mediaData = NULL) {

    switch (true) {

      case $entityType === 'node':

        $parsedMessage = $tokenService->replace($preparsedMessage . '<a href="/node/[node:nid]">', $entities);
        /** @noinspection NestedTernaryOperatorInspection */
        $message = $parsedMessage;
        $message .= $mediaData ? self::buildMediaMarkup($mediaData) : '';
        $message .= '</a>';

        return $message;
        break;

      case $entityType === 'status':

        $parsedMessage = $tokenService->replace($preparsedMessage . '<a href="/admin/structure/' . $entityType . '/[' . $entityType . ':id]">', $entities);
        /** @noinspection NestedTernaryOperatorInspection */
        $message = $parsedMessage;
        $message .= $mediaData ? self::buildMediaMarkup($mediaData) : 'Post';
        $message .= '</a>';

        return $message;

        break;

      case $entityType === 'user':

        break;

      case $entityType === 'flag':

        $returnMessage = self::handleMultipleEntities($tokenService, $preparsedMessage, $entities);

        return strlen($returnMessage) > 0 ? $returnMessage : "Error creating message";

        break;

    }

  }


  private static function buildMediaMarkup($mediaData) {

    $markup = '';

    foreach ($mediaData as $media) {
      $markup .= self::mediaTag($media->type, $media->path);
    }

    return $markup;
  }

  private static function mediaTag($type, $filePath) {
    //TODO put this into new method
    if ($type == 'image') {
      $type = 'img';
      return '<' . $type . ' src="' . str_replace('public://', '/sites/default/files/', $filePath) . '" / >';
    } else if ($type == 'youtube') {
      $filePath = str_replace('youtube://', 'http://www.youtube.com/embed/', $filePath);
      return '<iframe width="560" height="315" src="' . $filePath . '" frameborder="0"></iframe>';
    }
  }

  protected static function handleMultipleEntities(Token $tokenService, $message, $entities) {
    $tokens = $tokenService->scan($message);

    foreach($tokens as $key => $token) {
      foreach ($token as $type) {
        if (substr_count($message, $type) > 1) {
          foreach ($entities as $entityKey => $entityValue) {
            if ($entityValue instanceof \stdClass && count($entityValue->entities) > 1) {
              if ($key == $entityValue->type) {
                $messageArray = explode($type, $message);
                $stringRebuild = array();
                $replacements = array();
                $i = 0;
                foreach ($entityValue->entities as $entity) {
                  $stringRebuild[] = $tokenService->replace($message, array($key => $entity));
                  foreach (self::getWordRepeats($stringRebuild[$i]) as $word => $num) {
                    if ($num > 1 && !strpos($messageArray[1], $word)) {
                      $replacements[] = $word;
                    }
                  }
                  $i++;
                }
                if (count($replacements) == 2) {
                  $uid = $entityValue->entities[0]->id();
                  $uid_target = $entityValue->entities[1]->id();
                  $query = Database::getConnection()->query('
                    SELECT status
                    FROM heartbeat_friendship
                    WHERE uid = :uid AND uid_target = :uid_target', array(
                      ':uid' => $uid,
                      ':uid_target' => $uid_target
                    )
                  );
                  if ($query->fetchCol()[0] < 1) {
                    $messageArray[1] = ' has requested friendship with ';
                  }

                  $user1Link = Link::fromTextAndUrl($replacements[0], $entityValue->entities[0]->toUrl());
                  $user2Link = Link::fromTextAndUrl($replacements[1], $entityValue->entities[1]->toUrl());

                  $rebuiltMessage = $user1Link->toString() . $messageArray[1] . $user2Link->toString();
                  return $rebuiltMessage;
                }
              }
            }
          }
        }
      }
    }
    return null;
  }


  /**
   * Helper method to identify the number of times a word is repeated in a phrase
   *
   * @param $phrase
   * @return array
   */
  public static function getWordRepeats($phrase) {
    $counts = array();
      $words = explode(' ', $phrase);
      foreach ($words as $word) {
        if (!array_key_exists($word, $counts)) {
          $counts[$word] = 0;
        }
        $word = preg_replace("#[^a-zA-Z\-]#", "", $word);
        ++$counts[$word];
      }
    return $counts;
  }


  /**
   * Returns class of argument
   *
   * @param $field
   * @return string
   */
  public static function findClass($field) {
    return get_class($field);
  }


  /**
   * Returns an array of classes for array argument
   * @param $fields
   * @return array
   */
  public static function findAllMedia($fields) {
    return array_map(array(get_called_class(), 'findClass'), $fields);
  }


  /**
   * Returns all media types for an array of fields
   *
   * @param $fields
   * @return array
   * @throws \Drupal\Core\Entity\Exception\UndefinedLinkTemplateException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public static function mediaFieldTypes($fields) {

    $types = array();

    foreach ($fields as $field) {
      if ($field instanceof \Drupal\file\Plugin\Field\FieldType\FileFieldItemList) {
      $type = $field->getFieldDefinition()->getType();
        if ($field->getFieldDefinition()->getType() === 'image' ||
            $field->getFieldDefinition()->getType() === 'video' ||
            $field->getFieldDefinition()->getType() === 'audio') {

          $fieldValue = $field->getValue();
          $fileId = $fieldValue[0]['target_id'];
          $file = \Drupal::entityTypeManager()->getStorage('file')->load($fileId);

          if ($file !== NULL && is_object($file)) {
            $url = Url::fromUri($file->getFileUri());
            $posfind = strpos($url->getUri(), 'youtube://');
            if ($posfind !== 0 && $posfind !== false) {
              $mediaObject = self::createHeartbeatMedia($field->getFieldDefinition()->getType(), $url->getUri());
            } else {

              $mediaObject = self::createHeartbeatMedia('youtube', $url->getUri());
            }
            $types[] = $mediaObject;

          } else {
            continue;
          }
        }
      }
    }
    return $types;
  }


  /**
   * Parses a HeartbeatType message template and maps
   * variable values onto matching keywords
   *
   * @param $translatedMessage
   * @param $variables
   * @return string
   */
  public static function parseMessage($translatedMessage, $variables) {
    return strtr($translatedMessage, $variables);
  }

  public static function createHeartbeatMedia($type, $path) {

    $mediaObject = new \stdClass();
    $mediaObject->type = $type;
    $mediaObject->path = $path;

    return $mediaObject;
  }


  public static function getEntityNames($entityTypes) {
    $names = array();
    foreach ($entityTypes as $type) {

      if (($type->getBaseTable() === 'node') ||
          ($type->getBaseTable() === 'user') ||
        ($type->getBaseTable() === 'status')
        ||
          ($type->getStorageClass() !== NULL &&
            strpos($type->getStorageClass(), $type->getLabel()->getUntranslatedString())
          )
      ) {
        $names[] = $type->id();
      }
    }

    sort($names);

    return $names;
  }


  /**
   * Updates the friendship status of these two users
   *
   * @param $uid
   * @param $uid_target
   * @param $unixtime
   * @param $friendStatus
   * @return \Drupal\Core\Database\StatementInterface|int|null
   */
  public static function updateFriendship($uid, $uid_target, $unixtime, $friendStatus) {
//    $query = Database::getConnection()->upsert('heartbeat_friendship')
//      ->fields(array(
//        'uid' => $uid,
//        'uid_target' => $uid_target,
//        'created' => $unixtime,
//        'status' => $friendStatus,
//      ))
//      ->key('uid_relation');
//    return $query->execute();
    $update = Database::getConnection()->update('heartbeat_friendship')
      ->fields(['status' => $friendStatus])
      ->condition('uid', $uid, '=')
      ->condition('uid_target', $uid_target, '=');
    if (!$update->execute()) {
      $insert = Database::getConnection()->insert('heartbeat_friendship')
        ->fields([
          'uid' => $uid,
          'uid_target' => $uid_target,
          'created' => $unixtime,
          'status' => $friendStatus
        ]);
      if (!$insert->execute()) {
        \Drupal::logger('Heartbeat')->error('Unable to update friendship between %uid and %uid_target', array('%uid' => $uid, '%uid_target' => $uid_target));
      }
    }
    if ($friendStatus === 1) {
      $insert2 = Database::getConnection()->insert('heartbeat_friendship')
        ->fields([
          'uid' => $uid_target,
          'uid_target' => $uid,
          'created' => $unixtime,
          'status' => $friendStatus
        ]);
      if (!$insert2->execute()) {
        \Drupal::logger('Heartbeat')->error('Unable to update friendship between %uid and %uid_target', array('%uid' => $uid_target, '%uid_target' => $uid));
      }
    }
  }



  /**
   * Gets the Heartbeat user.
   *
   * @return int
   *   The uid of the Heartbeat's user.
   */
  public function getUid()
  {
    // TODO: Implement getUid() method.
  }

  /**
   * Sets the Heartbeat user.
   *
   * @param int uid
   *   The Heartbeat user.
   *
   */
  public function setUid($uid)
  {
    // TODO: Implement setUid() method.
  }

  /**
   * Gets the Heartbeat's associated node nid.
   *
   * @return int
   *   The nid of the Heartbeat's associated node.
   */
  public function getNid() {
    return $this->get('nid');
  }

  /**
   * Sets the Heartbeat user.
   *
   * @param int uid
   *   The Heartbeat user.
   *
   */
  public function setNid($nid) {
    $this->set('nid', $nid);
  }

}
