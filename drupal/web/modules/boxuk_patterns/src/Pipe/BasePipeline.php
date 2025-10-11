<?php

namespace Drupal\boxuk_patterns\Pipe;

use Drupal\boxuk_patterns\Pipe\Contract\PipeContract;

abstract class BasePipeline implements Contract\PipelineContract
{
  private object $object;

  private array $data = [];

  public function send(object $object): self {
    $this->object = $object;

    return $this;
  }

  public function through(array|string $pipes): self {
    if (is_string($pipes)) {
      $pipes = (array) $pipes;
    }

    $data = [];

    foreach ($pipes as $pipe) {
      if ($this->pipeIsValid($pipe)) {
        /** @var PipeContract $pipe */
        $pipe = new $pipe($this->object);

        $handledData = $pipe->handle();

        if ($handledData === []) {
          continue;
        }

        $data = $this->arrayMergeDeep($data, $handledData);
      }
    }

    $this->data = $data;

    return $this;
  }

  public function thenMerge(array $extraData): self {
    $this->data = $this->arrayMergeDeep($extraData, $this->data);

    return $this;
  }

  public function thenReturn(): array {
    return $this->data;
  }

  private function pipeIsValid(string $pipe): bool {
    return is_subclass_of($pipe, BasePipe::class);
  }

  private function arrayMergeDeep(array $array1, array $array2): array {
    foreach ($array2 as $key => $value) {
      // If the key exists in both arrays and both values are arrays, merge them recursively
      if (isset($array1[$key]) && is_array($array1[$key]) && is_array($value)) {
        $array1[$key] = $this->arrayMergeDeep($array1[$key], $value);
      }
      // If numeric key, append instead of overwrite
      elseif (is_numeric($key)) {
        $array1[] = $value;
      }
      // Otherwise overwrite/add the value
      else {
        $array1[$key] = $value;
      }
    }

    return $array1;
  }
}
