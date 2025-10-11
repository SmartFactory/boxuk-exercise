<?php

namespace Drupal\boxuk_patterns\Pipe;

use Drupal\boxuk_patterns\Pipe\Contract\PipeContract;

abstract class BasePipe implements PipeContract
{
  public function __construct(
    /** @phpstan-var object */
    protected $object
  ) {
  }

  public function getObject(): object {
    /** @phpstan-ignore-next-line */
    return $this->object;
  }
}
