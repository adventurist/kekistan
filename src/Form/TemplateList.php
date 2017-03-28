<?php

namespace Drupal\heartbeat8\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Drupal\user\UserData;

/**
 * Class TemplateList.
 *
 * @package Drupal\heartbeat8\Form
 */
class TemplateList extends FormBase {

  /**
   * Drupal\Core\Form\FormBuilder definition.
   *
   * @var Drupal\Core\Form\FormBuilder
   */
  protected $form_builder;

  /**
   * Symfony\Component\HttpFoundation\Request definition.
   *
   * @var Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Drupal\user\UserData definition.
   *
   * @var Drupal\user\UserData
   */
  protected $user_data;
  public function __construct(
    FormBuilder $form_builder,
    UserData $user_data
  ) {
    $this->form_builder = $form_builder;
    $this->user_data = $user_data;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('user.data')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'template_list';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $templateNames = ['One', 'Two', 'Three', 'Four'];

    foreach ($templateNames as $templateName) {
      $form[$templateName] = array(
        'Template' => array(
          '#type' => 'details',
          '#title' => t('Wut'),
          '#collapsible' => TRUE,
          '#collapsed' => TRUE,
          '#states' => array(
            'expanded' => array(
              ':input[name="Template name"]' => array('value' => 'expand'),
            ),
          ),
        ),
        'notes' => array(
        '#type' => 'textarea',
        '#title' => $this->t($templateName . ' notes'),
        '#description' => $this->t('Additional notes'),
        )
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
