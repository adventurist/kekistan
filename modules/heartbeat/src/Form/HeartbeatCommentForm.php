<?php

namespace Drupal\heartbeat\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Comment\Entity\Comment;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\PrependCommand;

/**
 * Class HeartbeatCommentForm.
 *
 * @property  entity
 * @package Drupal\heartbeat\Form
 */
class HeartbeatCommentForm extends FormBase {
  protected $entityId;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'heartbeat_comment_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['comment_body'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Comment Body'),
    );

    $form['post'] = array(
      '#type' => 'submit',
      '#description' => 'Comment',
      '#value' => t('Comment'),
      '#ajax' => [
        'callback' => '::commentAjaxSubmit',
        'progress' => array(
          'type' => 'throbber',
          'message' => t('Posting Comment'),
        ),
      ]
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function commentAjaxSubmit(array &$form, FormStateInterface $form_state) {

    $commentBody = $form_state->getValue('comment_body');
    $config = \Drupal::config('heartbeat_comment.settings');


    $comment = Comment::create([
      'entity_type' => 'heartbeat',
      'entity_id' => $config->get('entity_id'),
      'field_name' => 'comment',
      'comment_body' => $commentBody,
      'comment_type' => 'comment',
      'subject' => 'Heartbeat Comment',
    ]);

    if ($comment->save()) {

      $response = new AjaxResponse();
      $response->addCommand(new PrependCommand(
        '#heartbeat-' . $config->get('entity_id') . ' .heartbeat-comments',
        '<div id="heartbeat-comment-' . $comment->id() . '">' . $commentBody . ' <br></div>'));

      return $response;

    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
