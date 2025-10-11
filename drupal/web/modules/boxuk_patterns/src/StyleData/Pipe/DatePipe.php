<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

/**
 * Pipe to extract and format date information from the node.
 *
 * This is an example pipe that demonstrates how to access entity timestamps
 * and format them for display in the template.
 */
class DatePipe extends BasePipe {

  /**
   * {@inheritdoc}
   */
  public function handle(): array {
    $object = $this->getObject();

    // Ensure we have a node entity before accessing its properties.
    if (!$object instanceof NodeInterface) {
      return [];
    }

    $created = $object->getCreatedTime();
    $changed = $object->getChangedTime();

    return [
      'dates' => [
        'created' => [
          'timestamp' => $created,
          'formatted' => \date('F j, Y', $created),
          'short' => \date('Y-m-d', $created),
        ],
        'changed' => [
          'timestamp' => $changed,
          'formatted' => \date('F j, Y', $changed),
          'short' => \date('Y-m-d', $changed),
        ],
      ],
    ];
  }

}
