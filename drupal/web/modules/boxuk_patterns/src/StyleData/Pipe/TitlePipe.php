<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

/**
 * Pipe to extract and format the node title.
 *
 * This is an example pipe that demonstrates how to access entity data
 * and return it in a custom format for the template.
 */
class TitlePipe extends BasePipe {

  /**
   * {@inheritdoc}
   */
  public function handle(): array {
    $object = $this->getObject();

    // Ensure we have a node entity before accessing its properties.
    if (!$object instanceof NodeInterface) {
      return [];
    }

    return [
      'title' => [
        'text' => $object->getTitle(),
        'formatted' => strtoupper($object->getTitle()),
      ],
    ];
  }

}
