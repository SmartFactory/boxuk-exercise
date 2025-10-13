<?php

namespace Drupal\Tests\boxuk_patterns\Unit\StyleData\Pipe;

use Drupal\boxuk_patterns\Entity\Node\ArticleInterface;
use Drupal\boxuk_patterns\StyleData\Pipe\ReadingStatsPipe;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the ReadingStatsPipe class.
 *
 * @group boxuk_patterns
 * @coversDefaultClass \Drupal\boxuk_patterns\StyleData\Pipe\ReadingStatsPipe
 */
class ReadingStatsPipeTest extends UnitTestCase {

  /**
   * Creates a mock article object with reading stats methods.
   *
   * @param int $wordCount
   *   The word count to return.
   * @param int $readingTime
   *   The reading time to return.
   *
   * @return \Drupal\boxuk_patterns\Entity\Node\ArticleInterface
   *   A mock article object.
   */
  private function createMockArticle(int $wordCount, int $readingTime): ArticleInterface {
    $article = $this->createMock(ArticleInterface::class);
    $article->method('getWordCount')->willReturn($wordCount);
    $article->method('getReadingTime')->willReturn($readingTime);

    return $article;
  }

  /**
   * Test handle returns reading stats data for a valid article.
   *
   * @covers ::handle
   * @covers ::pluralize
   */
  public function testHandleReturnsReadingStatsData(): void {
    $article = $this->createMockArticle(1234, 6);

    // Create pipe and get result.
    $pipe = new ReadingStatsPipe($article);
    $result = $pipe->handle();

    // Assert the structure and content.
    $this->assertIsArray($result);
    $this->assertArrayHasKey('reading_stats', $result);

    $stats = $result['reading_stats'];
    $this->assertEquals(1234, $stats['word_count']);
    $this->assertEquals('1,234 words', $stats['word_count_formatted']);
    $this->assertEquals(6, $stats['reading_time']);
    $this->assertEquals('6 min read', $stats['reading_time_formatted']);
    $this->assertEquals(200, $stats['reading_speed']);
  }

  /**
   * Test handle returns empty array for non-article object.
   *
   * @covers ::handle
   */
  public function testHandleReturnsEmptyForNonArticle(): void {
    $object = new \stdClass();
    $pipe = new ReadingStatsPipe($object);

    $result = $pipe->handle();

    $this->assertIsArray($result);
    $this->assertEmpty($result);
  }

  /**
   * Test handle returns empty array for zero word count.
   *
   * @covers ::handle
   */
  public function testHandleReturnsEmptyForZeroWordCount(): void {
    $article = $this->createMockArticle(0, 0);

    // Create pipe and get result.
    $pipe = new ReadingStatsPipe($article);
    $result = $pipe->handle();

    // Assert empty result.
    $this->assertIsArray($result);
    $this->assertEmpty($result);
  }

  /**
   * Test pluralize helper with singular count.
   *
   * @covers ::handle
   * @covers ::pluralize
   */
  public function testHandleUsesCorrectSingularForm(): void {
    $article = $this->createMockArticle(1, 1);

    // Create pipe and get result.
    $pipe = new ReadingStatsPipe($article);
    $result = $pipe->handle();

    // Assert singular form is used.
    $this->assertEquals('1 word', $result['reading_stats']['word_count_formatted']);
  }

  /**
   * Test pluralize helper with plural count.
   *
   * @covers ::handle
   * @covers ::pluralize
   */
  public function testHandleUsesCorrectPluralForm(): void {
    $article = $this->createMockArticle(2, 1);

    // Create pipe and get result.
    $pipe = new ReadingStatsPipe($article);
    $result = $pipe->handle();

    // Assert plural form is used.
    $this->assertEquals('2 words', $result['reading_stats']['word_count_formatted']);
  }

  /**
   * Test handle formats large numbers correctly.
   *
   * @covers ::handle
   * @covers ::pluralize
   */
  public function testHandleFormatsLargeNumbers(): void {
    $article = $this->createMockArticle(1234567, 6173);

    // Create pipe and get result.
    $pipe = new ReadingStatsPipe($article);
    $result = $pipe->handle();

    // Assert number formatting.
    $this->assertEquals('1,234,567 words', $result['reading_stats']['word_count_formatted']);
  }

}
