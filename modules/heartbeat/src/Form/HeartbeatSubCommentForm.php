<?php

namespace Drupal\heartbeat\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Comment\Entity\Comment;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;

/**
 * Class HeartbeatSubCommentForm.
 *
 * @package Drupal\heartbeat\Form
 */
class HeartbeatSubCommentForm extends FormBase {
  protected $entityId;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'heartbeat_sub_comment_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['comment_body'] = array(
      '#type' => 'textarea',
    );

    $form['post'] = array(
      '#type' => 'submit',
      '#description' => 'Comment',
      '#value' => t('Reply'),
      '#ajax' => [
        'callback' => '::commentAjaxSubmit',
        'progress' => array(
          'type' => 'throbber',
          'message' => t('Posting Reply'),
        ),
      ]
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function commentAjaxSubmit(array &$form, FormStateInterface $form_state) {

    if (\Drupal::currentUser()->isAuthenticated()) {
      $commentBody = $form_state->getValue('comment_body');
      $config = \Drupal::config('heartbeat_comment.settings');

      if (strlen(trim($commentBody)) > 1) {
        $comment = Comment::create([
          'entity_type' => 'comment',
          'entity_id' => $config->get('cid'),
          'pid' => $config->get('cid'),
          'field_name' => 'comment',
          'comment_body' => $commentBody,
          'comment_type' => 'comment',
          'subject' => 'Heartbeat Comment',
          'uid' => \Drupal::currentUser()->id(),
        ]);

        if ($comment->save()) {
          $userview= user_view($comment->getOwner(), 'comment');

          $response = new AjaxResponse();
          $response->addCommand(new AppendCommand(
              '#heartbeat-comment-' . $config->get('cid') . ' .sub-comment',
              '<div class="heartbeat-subcomment" id="sub-comment-' . $comment->id() . '"><span class="comment-owner"><span class="comment-username">' . \Drupal::currentUser()->getAccountName() . '</span>' . render($userview) . '<span class"comment-ago">1 sec ago</span></span><span class="comment-body">' . $commentBody . '</span><span class="sub-comment"><a href="/heartbeat/subcommentrequest/' . $cid . '" class="button button-action use-ajax">Reply</a></span></div>')
          );
          return $response;
        }
      }
    }
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
