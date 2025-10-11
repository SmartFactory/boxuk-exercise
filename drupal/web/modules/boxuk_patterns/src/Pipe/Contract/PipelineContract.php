<?php

namespace Drupal\boxuk_patterns\Pipe\Contract;

interface PipelineContract
{
  public static function create(): self;

  public function send(object $object): self;

  public function through(array $pipes): self;

  public function thenMerge(array $extraData): self;

  public function thenReturn(): array;
}
