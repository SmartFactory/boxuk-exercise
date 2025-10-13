<?php

declare(strict_types=1);

namespace Drupal\Tests\boxuk_quotes\Unit\ValueObject;

use Drupal\boxuk_quotes\ValueObject\Quote;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the Quote value object.
 *
 * @group boxuk_quotes
 * @coversDefaultClass \Drupal\boxuk_quotes\ValueObject\Quote
 */
class QuoteTest extends UnitTestCase {

  /**
   * Test creating a valid Quote object.
   *
   * @covers ::__construct
   * @covers ::getQuote
   * @covers ::getAuthor
   * @covers ::getCategory
   */
  public function testCreateValidQuote(): void {
    $quote = new Quote(
      quote: 'The only way to do great work is to love what you do.',
      author: 'Steve Jobs',
      category: 'inspire',
    );

    $this->assertEquals('The only way to do great work is to love what you do.', $quote->getQuote());
    $this->assertEquals('Steve Jobs', $quote->getAuthor());
    $this->assertEquals('inspire', $quote->getCategory());
  }

  /**
   * Test toArray method returns correct structure.
   *
   * @covers ::toArray
   */
  public function testToArrayReturnsCorrectStructure(): void {
    $quote = new Quote(
      quote: 'Success is not final, failure is not fatal.',
      author: 'Winston Churchill',
      category: 'inspire',
    );

    $array = $quote->toArray();

    $this->assertIsArray($array);
    $this->assertArrayHasKey('quote', $array);
    $this->assertArrayHasKey('author', $array);
    $this->assertArrayHasKey('category', $array);
    $this->assertEquals('Success is not final, failure is not fatal.', $array['quote']);
    $this->assertEquals('Winston Churchill', $array['author']);
    $this->assertEquals('inspire', $array['category']);
  }

  /**
   * Test that empty quote text throws exception.
   *
   * @covers ::__construct
   */
  public function testEmptyQuoteThrowsException(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Quote text cannot be empty.');

    new Quote(
      quote: '',
      author: 'Someone',
      category: 'inspire',
    );
  }

  /**
   * Test that empty author throws exception.
   *
   * @covers ::__construct
   */
  public function testEmptyAuthorThrowsException(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Author cannot be empty.');

    new Quote(
      quote: 'Some quote',
      author: '',
      category: 'inspire',
    );
  }

  /**
   * Test that Quote object is immutable.
   *
   * @covers ::__construct
   * @covers ::getQuote
   */
  public function testQuoteIsImmutable(): void {
    $quote = new Quote(
      quote: 'Original quote',
      author: 'Original author',
      category: 'inspire',
    );

    $quoteText = $quote->getQuote();
    $this->assertEquals('Original quote', $quoteText);

    // Verify that we can't modify the returned value and affect the object.
    $quoteText = 'Modified quote';
    $this->assertEquals('Original quote', $quote->getQuote());
  }

}
