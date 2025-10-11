<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

/**
 * Pipe to extract author information from the node.
 *
 * This is an example pipe that demonstrates how to access related entities
 * (like the node author) and return their data.
 */
class AuthorPipe extends BasePipe {

  /**
   * {@inheritdoc}
   */
  public function handle(): array {
    $object = $this->getObject();

    // Ensure we have a node entity before accessing its properties.
    if (!$object instanceof NodeInterface) {
      return [];
    }

    $author = $object->getOwner();

    if (!$author) {
      return [];
    }

    return [
      'author' => [
        'name' => $author->getDisplayName(),
        'id' => $author->id(),
      ],
    ];
  }

}
