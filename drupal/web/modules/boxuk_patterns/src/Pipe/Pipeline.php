<?php

namespace Drupal\boxuk_patterns\Pipe;

/**
 * Pipeline implementation for processing data through pipes.
 *
 * This class provides a fluent interface for passing an object through
 * a series of pipes, collecting and merging the results.
 */
final class Pipeline extends BasePipeline {

  /**
   * Creates a new Pipeline instance.
   *
   * @return static
   *   A new Pipeline instance.
   */
  public static function create(): self {
    return new self();
  }

}
