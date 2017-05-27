<?php

namespace Drupal\heartbeat\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Random;
use Drupal\views\Plugin\views\field\MultiItemsFieldHandlerInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("heartbeat_message_field")
 */
class HeartbeatMessageField extends FieldPluginBase implements MultiItemsFieldHandlerInterface {

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['hide_alter_empty'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * Renders a single item of a row.
   *
   * @param int $count
   *   The index of the item inside the row.
   * @param mixed $item
   *   The item for the field to render.
   *
   * @return string
   *   The rendered output.
   */
  public function render_item($count, $item) {
    return check_markup($item['value'], 'full_html');
  }

  /**
   * Gets an array of items for the field.
   *
   * @param \Drupal\views\ResultRow $values
   *   The result row object containing the values.
   *
   * @return array
   *   An array of items for the field.
   */
  public function getItems(ResultRow $values) {
  }

  /**
   * Render all items in this field together.
   *
   * @param array $items
   *   The items provided by getItems for a single row.
   *
   * @return string
   *   The rendered items.
   */
  public function renderItems($items)
  {
    // TODO: Implement renderItems() method.
  }
}
