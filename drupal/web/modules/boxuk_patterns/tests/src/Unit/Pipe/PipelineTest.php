<?php

declare(strict_types=1);

namespace Drupal\Tests\boxuk_patterns\Unit\Pipe;

use Drupal\boxuk_patterns\Pipe\Pipeline;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the Pipeline class.
 *
 * @group boxuk_patterns
 * @coversDefaultClass \Drupal\boxuk_patterns\Pipe\Pipeline
 */
class PipelineTest extends UnitTestCase {

  /**
   * Test the create factory method returns a new Pipeline instance.
   *
   * @covers ::create
   */
  public function testCreateReturnsNewInstance(): void {
    $pipeline = Pipeline::create();

    $this->assertInstanceOf(Pipeline::class, $pipeline);
  }

  /**
   * Test that multiple create calls return different instances.
   *
   * @covers ::create
   */
  public function testCreateReturnsUniqueInstances(): void {
    $pipeline1 = Pipeline::create();
    $pipeline2 = Pipeline::create();

    $this->assertNotSame($pipeline1, $pipeline2);
  }

}
