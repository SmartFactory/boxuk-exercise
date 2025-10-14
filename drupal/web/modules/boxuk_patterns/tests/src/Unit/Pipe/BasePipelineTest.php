<?php

namespace Drupal\Tests\boxuk_patterns\Unit\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\boxuk_patterns\Pipe\Pipeline;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the BasePipeline class.
 *
 * @group boxuk_patterns
 * @coversDefaultClass \Drupal\boxuk_patterns\Pipe\BasePipeline
 */
class BasePipelineTest extends UnitTestCase {

  /**
   * Test the send method sets the object.
   *
   * @covers ::send
   */
  public function testSendSetsObject(): void {
    $pipeline = Pipeline::create();
    $object = new \stdClass();
    $object->name = 'test';

    $result = $pipeline->send($object);

    // Should return self for fluent interface
    $this->assertInstanceOf(Pipeline::class, $result);
  }

  /**
   * Test the through method with a single valid pipe.
   *
   * @covers ::through
   * @covers ::pipeIsValid
   */
  public function testThroughWithValidPipe(): void {
    $pipeline = Pipeline::create();
    $object = new \stdClass();

    $result = $pipeline
      ->send($object)
      ->through([TestPipe::class]);

    // Should return self for fluent interface
    $this->assertInstanceOf(Pipeline::class, $result);
  }

  /**
   * Test the through method with multiple pipes.
   *
   * @covers ::through
   * @covers ::arrayMergeDeep
   */
  public function testThroughWithMultiplePipes(): void {
    $pipeline = Pipeline::create();
    $object = new \stdClass();

    $result = $pipeline
      ->send($object)
      ->through([TestPipe::class, TestPipe2::class])
      ->thenReturn();

    $this->assertEquals([
      'test' => 'value',
      'test2' => 'value2',
    ], $result);
  }

  /**
   * Test deep array merging.
   *
   * @covers ::through
   * @covers ::arrayMergeDeep
   */
  public function testDeepArrayMerge(): void {
    $pipeline = Pipeline::create();
    $object = new \stdClass();

    $result = $pipeline
      ->send($object)
      ->through([NestedPipe1::class, NestedPipe2::class])
      ->thenReturn();

    $this->assertEquals([
      'level1' => [
        'level2' => [
          'key1' => 'value1',
          'key2' => 'value2',
        ],
        'other' => 'data',
      ],
    ], $result);
  }

  /**
   * Test that numeric keys are appended, not overwritten.
   *
   * @covers ::arrayMergeDeep
   */
  public function testNumericKeysAreAppended(): void {
    $pipeline = Pipeline::create();
    $object = new \stdClass();

    $result = $pipeline
      ->send($object)
      ->through([NumericKeyPipe1::class, NumericKeyPipe2::class])
      ->thenReturn();

    $this->assertEquals([
      'items' => ['item1', 'item2', 'item3', 'item4'],
    ], $result);
  }

  /**
   * Test thenMerge adds extra data.
   *
   * @covers ::thenMerge
   */
  public function testThenMergeAddsExtraData(): void {
    $pipeline = Pipeline::create();
    $object = new \stdClass();

    $result = $pipeline
      ->send($object)
      ->through([TestPipe::class])
      ->thenMerge(['extra' => 'data'])
      ->thenReturn();

    $this->assertEquals([
      'extra' => 'data',
      'test' => 'value',
    ], $result);
  }

  /**
   * Test thenReturn returns the processed data.
   *
   * @covers ::thenReturn
   */
  public function testThenReturnReturnsData(): void {
    $pipeline = Pipeline::create();
    $object = new \stdClass();

    $result = $pipeline
      ->send($object)
      ->through([TestPipe::class])
      ->thenReturn();

    $this->assertIsArray($result);
    $this->assertEquals(['test' => 'value'], $result);
  }

  /**
   * Test that invalid pipes are skipped.
   *
   * @covers ::pipeIsValid
   */
  public function testInvalidPipesAreSkipped(): void {
    $pipeline = Pipeline::create();
    $object = new \stdClass();

    // This should not throw an exception, invalid pipes are just skipped
    $result = $pipeline
      ->send($object)
      ->through(['InvalidClass'])
      ->thenReturn();

    $this->assertEquals([], $result);
  }

  /**
   * Test that pipes returning empty arrays don't affect the result.
   *
   * @covers ::through
   */
  public function testEmptyPipeResultsAreSkipped(): void {
    $pipeline = Pipeline::create();
    $object = new \stdClass();

    $result = $pipeline
      ->send($object)
      ->through([EmptyPipe::class, TestPipe::class])
      ->thenReturn();

    $this->assertEquals(['test' => 'value'], $result);
  }

  /**
   * Test fluent interface works correctly.
   *
   * @covers ::send
   * @covers ::through
   * @covers ::thenMerge
   */
  public function testFluentInterface(): void {
    $pipeline = Pipeline::create();
    $object = new \stdClass();

    // All methods should return $this for chaining
    $step1 = $pipeline->send($object);
    $this->assertInstanceOf(Pipeline::class, $step1);

    $step2 = $step1->through([TestPipe::class]);
    $this->assertInstanceOf(Pipeline::class, $step2);

    $step3 = $step2->thenMerge(['extra' => 'data']);
    $this->assertInstanceOf(Pipeline::class, $step3);
  }

}

/**
 * Test pipe that returns simple data.
 */
class TestPipe extends BasePipe {

  public function handle(): array {
    return ['test' => 'value'];
  }

}

/**
 * Second test pipe.
 */
class TestPipe2 extends BasePipe {

  public function handle(): array {
    return ['test2' => 'value2'];
  }

}

/**
 * Test pipe with nested array structure.
 */
class NestedPipe1 extends BasePipe {

  public function handle(): array {
    return [
      'level1' => [
        'level2' => [
          'key1' => 'value1',
        ],
      ],
    ];
  }

}

/**
 * Second nested pipe that should merge deeply.
 */
class NestedPipe2 extends BasePipe {

  public function handle(): array {
    return [
      'level1' => [
        'level2' => [
          'key2' => 'value2',
        ],
        'other' => 'data',
      ],
    ];
  }

}

/**
 * Test pipe with numeric keys.
 */
class NumericKeyPipe1 extends BasePipe {

  public function handle(): array {
    return ['items' => ['item1', 'item2']];
  }

}

/**
 * Second numeric key pipe.
 */
class NumericKeyPipe2 extends BasePipe {

  public function handle(): array {
    return ['items' => ['item3', 'item4']];
  }

}

/**
 * Pipe that returns empty array.
 */
class EmptyPipe extends BasePipe {

  public function handle(): array {
    return [];
  }

}
