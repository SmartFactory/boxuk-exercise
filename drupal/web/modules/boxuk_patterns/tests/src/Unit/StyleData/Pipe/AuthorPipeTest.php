<?php

declare(strict_types=1);

namespace Drupal\Tests\boxuk_patterns\Unit\StyleData\Pipe;

use Drupal\boxuk_patterns\StyleData\Pipe\AuthorPipe;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;

/**
 * Tests for the AuthorPipe class.
 *
 * @group boxuk_patterns
 * @coversDefaultClass \Drupal\boxuk_patterns\StyleData\Pipe\AuthorPipe
 */
class AuthorPipeTest extends UnitTestCase {

  /**
   * Test handle returns author data for a valid node with author.
   *
   * @covers ::handle
   */
  public function testHandleReturnsAuthorData(): void {
    $author = $this->createMock(UserInterface::class);
    $author->expects($this->once())
      ->method('getDisplayName')
      ->willReturn('John Doe');
    $author->expects($this->once())
      ->method('id')
      ->willReturn('42');

    $node = $this->createMock(NodeInterface::class);
    $node->expects($this->once())
      ->method('getOwner')
      ->willReturn($author);

    $pipe = new AuthorPipe($node);
    $result = $pipe->handle();

    $this->assertIsArray($result);
    $this->assertArrayHasKey('author', $result);
    $this->assertIsArray($result['author']);
    $this->assertEquals('John Doe', $result['author']['name']);
    $this->assertEquals('42', $result['author']['id']);
  }

  /**
   * Test handle returns empty array for non-node object.
   *
   * @covers ::handle
   */
  public function testHandleReturnsEmptyForNonNode(): void {
    $object = new \stdClass();
    $pipe = new AuthorPipe($object);

    $result = $pipe->handle();

    $this->assertIsArray($result);
    $this->assertEmpty($result);
  }

  /**
   * Test handle returns empty array when node has no author.
   *
   * @covers ::handle
   */
  public function testHandleReturnsEmptyWhenNoAuthor(): void {
    $node = $this->createMock(NodeInterface::class);
    $node->expects($this->once())
      ->method('getOwner')
      ->willReturn(NULL);

    $pipe = new AuthorPipe($node);
    $result = $pipe->handle();

    $this->assertIsArray($result);
    $this->assertEmpty($result);
  }

}
