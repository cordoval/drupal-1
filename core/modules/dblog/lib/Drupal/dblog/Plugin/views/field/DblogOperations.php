<?php

/**
 * @file
 * Contains \Drupal\dblog\Plugin\views\field\DblogOperations.
 */

namespace Drupal\dblog\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;

/**
 * Provides a field handler that renders operation link markup.
 *
 * @ingroup views_field_handlers
 *
 * @PluginID("dblog_operations")
 */
class DblogOperations extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function clickSortable() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = $this->getValue($values);
    return $this->sanitizeValue($value, 'xss_admin');
  }

}
