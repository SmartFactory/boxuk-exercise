<?php

declare(strict_types=1);

namespace Drupal\Tests\boxuk_patterns\Unit\StyleData\Pipe;

use Drupal\boxuk_patterns\StyleData\Pipe\TitlePipe;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the TitlePipe class.
 *
 * @group boxuk_patterns
 * @coversDefaultClass \Drupal\boxuk_patterns\StyleData\Pipe\TitlePipe
 */
class TitlePipeTest extends UnitTestCase {

  /**
   * Test handle returns title data for a valid node.
   *
   * @covers ::handle
   */
  public function testHandleReturnsTitleData(): void {
    $node = $this->createMock(NodeInterface::class);
    $node->expects($this->exactly(2))
      ->method('getTitle')
      ->willReturn('Test Article Title');

    $pipe = new TitlePipe($node);
    $result = $pipe->handle();

    $this->assertIsArray($result);
    $this->assertArrayHasKey('title', $result);
    $this->assertIsArray($result['title']);
    $this->assertEquals('Test Article Title', $result['title']['text']);
    $this->assertEquals('TEST ARTICLE TITLE', $result['title']['formatted']);
  }

  /**
   * Test handle returns empty array for non-node object.
   *
   * @covers ::handle
   */
  public function testHandleReturnsEmptyForNonNode(): void {
    $object = new \stdClass();
    $pipe = new TitlePipe($object);

    $result = $pipe->handle();

    $this->assertIsArray($result);
    $this->assertEmpty($result);
  }

  /**
   * Test formatted title is always uppercase.
   *
   * @covers ::handle
   */
  public function testFormattedTitleIsUppercase(): void {
    $node = $this->createMock(NodeInterface::class);
    $node->expects($this->exactly(2))
      ->method('getTitle')
      ->willReturn('lowercase title');

    $pipe = new TitlePipe($node);
    $result = $pipe->handle();

    $this->assertEquals('LOWERCASE TITLE', $result['title']['formatted']);
  }

}
