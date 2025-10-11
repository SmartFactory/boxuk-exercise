<?php

declare(strict_types=1);

namespace Drupal\Tests\boxuk_patterns\Unit\StyleData\Pipe;

use Drupal\boxuk_patterns\StyleData\Pipe\HeaderPipe;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the HeaderPipe class.
 *
 * @group boxuk_patterns
 * @coversDefaultClass \Drupal\boxuk_patterns\StyleData\Pipe\HeaderPipe
 */
class HeaderPipeTest extends UnitTestCase {

  /**
   * Test handle returns header data for a valid node.
   *
   * @covers ::handle
   */
  public function testHandleReturnsHeaderForNode(): void {
    $node = $this->createMock(NodeInterface::class);
    $pipe = new HeaderPipe($node);

    $result = $pipe->handle();

    $this->assertIsArray($result);
    $this->assertArrayHasKey('header', $result);
    $this->assertEquals('This is the header', $result['header']);
  }

  /**
   * Test handle returns empty array for non-node object.
   *
   * @covers ::handle
   */
  public function testHandleReturnsEmptyForNonNode(): void {
    $object = new \stdClass();
    $pipe = new HeaderPipe($object);

    $result = $pipe->handle();

    $this->assertIsArray($result);
    $this->assertEmpty($result);
  }

}
