<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

/**
 * Pipe to add a custom header to the template.
 *
 * This is a simple example pipe that demonstrates the basic structure.
 * It returns static data, but could be enhanced to use node data.
 */
class HeaderPipe extends BasePipe {

  /**
   * {@inheritdoc}
   */
  public function handle(): array {
    $object = $this->getObject();

    // Example: You can access the node entity if needed
    if ($object instanceof NodeInterface) {
      // You could customize the header based on node properties
      // For now, we'll keep it simple
      return [
        'header' => 'This is the header',
      ];
    }

    return [];
  }

}
