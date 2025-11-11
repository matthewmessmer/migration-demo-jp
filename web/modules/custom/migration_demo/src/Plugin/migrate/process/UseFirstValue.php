<?php

namespace Drupal\migration_demo\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Provides a use_first_value plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   field_name:
 *     plugin: use_first_value
 *       source:
 *         - value0
 *         - value1
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "use_first_value"
 * )
 */
class UseFirstValue extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Loop through values and return the first non-empty item.
    foreach ($value as $item) {
      if (!empty($item)) {
        return $item;
      }
    }

    return $value[0];
  }

}
