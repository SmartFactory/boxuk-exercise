<?php

namespace Drupal\Tests\boxuk_patterns\Unit\StyleData\Pipe;

use Drupal\boxuk_patterns\StyleData\Pipe\DatePipe;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the DatePipe class.
 *
 * @group boxuk_patterns
 * @coversDefaultClass \Drupal\boxuk_patterns\StyleData\Pipe\DatePipe
 */
class DatePipeTest extends UnitTestCase {

  /**
   * Test handle returns date data for a valid node.
   *
   * @covers ::handle
   */
  public function testHandleReturnsDateData(): void {
    $createdTime = strtotime('2024-01-15 10:30:00');
    $changedTime = strtotime('2024-02-20 14:45:00');

    $node = $this->createMock(NodeInterface::class);
    $node->expects($this->once())
      ->method('getCreatedTime')
      ->willReturn($createdTime);
    $node->expects($this->once())
      ->method('getChangedTime')
      ->willReturn($changedTime);

    $pipe = new DatePipe($node);
    $result = $pipe->handle();

    $this->assertIsArray($result);
    $this->assertArrayHasKey('dates', $result);
    $this->assertIsArray($result['dates']);

    // Check created date
    $this->assertArrayHasKey('created', $result['dates']);
    $this->assertEquals($createdTime, $result['dates']['created']['timestamp']);
    $this->assertEquals('January 15, 2024', $result['dates']['created']['formatted']);
    $this->assertEquals('2024-01-15', $result['dates']['created']['short']);

    // Check changed date
    $this->assertArrayHasKey('changed', $result['dates']);
    $this->assertEquals($changedTime, $result['dates']['changed']['timestamp']);
    $this->assertEquals('February 20, 2024', $result['dates']['changed']['formatted']);
    $this->assertEquals('2024-02-20', $result['dates']['changed']['short']);
  }

  /**
   * Test handle returns empty array for non-node object.
   *
   * @covers ::handle
   */
  public function testHandleReturnsEmptyForNonNode(): void {
    $object = new \stdClass();
    $pipe = new DatePipe($object);

    $result = $pipe->handle();

    $this->assertIsArray($result);
    $this->assertEmpty($result);
  }

  /**
   * Test date formatting is consistent.
   *
   * @covers ::handle
   */
  public function testDateFormattingIsConsistent(): void {
    $timestamp = strtotime('2024-12-31 23:59:59');

    $node = $this->createMock(NodeInterface::class);
    $node->expects($this->once())
      ->method('getCreatedTime')
      ->willReturn($timestamp);
    $node->expects($this->once())
      ->method('getChangedTime')
      ->willReturn($timestamp);

    $pipe = new DatePipe($node);
    $result = $pipe->handle();

    // Both created and changed should have the same format when timestamps are the same
    $this->assertEquals(
      $result['dates']['created']['formatted'],
      $result['dates']['changed']['formatted']
    );
    $this->assertEquals('December 31, 2024', $result['dates']['created']['formatted']);
  }

}
