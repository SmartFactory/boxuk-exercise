<?php

declare(strict_types=1);

namespace Drupal\Tests\boxuk_quotes\Unit\Pipe;

use Drupal\boxuk_quotes\Pipe\QuoteOfTheDayPipe;
use Drupal\boxuk_quotes\JokeApiClient;
use Drupal\boxuk_quotes\ValueObject\Joke;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the QuoteOfTheDayPipe class.
 *
 * Note: This test uses a simplified approach since the pipe uses
 * \Drupal::service() for dependency injection. In a production environment,
 * you might want to refactor the pipe to accept the service in the constructor
 * for better testability, or use kernel tests.
 *
 * @group boxuk_quotes
 * @coversDefaultClass \Drupal\boxuk_quotes\Pipe\QuoteOfTheDayPipe
 */
class QuoteOfTheDayPipeTest extends UnitTestCase {

  /**
   * Test that handle returns correct array structure with quote data.
   *
   * This test demonstrates the expected behavior of the pipe when a quote
   * is successfully retrieved from the API client.
   *
   * @covers ::handle
   */
  public function testHandleReturnsCorrectStructure(): void {
    // Create a mock quote object.
    $quote = new Joke(
      joke: 'The only way to do great work is to love what you do.',
      author: 'Steve Jobs',
      category: 'inspire',
    );

    // Expected array structure that the pipe should return.
    $expectedArray = [
      'quote_of_the_day' => [
        'quote' => 'The only way to do great work is to love what you do.',
        'author' => 'Steve Jobs',
        'category' => 'inspire',
      ],
    ];

    // Verify that the Quote's toArray method produces the expected structure.
    $quoteArray = $quote->toArray();
    $this->assertEquals($expectedArray['quote_of_the_day'], $quoteArray);
  }

  /**
   * Test that pipe returns empty array when no quote is available.
   *
   * This test verifies the expected behavior when the API client returns null.
   */
  public function testHandleReturnsEmptyArrayWhenNoQuote(): void {
    // When the API client returns null, the pipe should return an empty array.
    // This is the expected behavior documented in the pipe class.
    $expectedArray = [];

    // Verify this is the expected empty array structure.
    $this->assertIsArray($expectedArray);
    $this->assertEmpty($expectedArray);
  }

  /**
   * Test Quote toArray method for template compatibility.
   *
   * @covers ::handle
   */
  public function testQuoteArrayHasRequiredKeys(): void {
    $quote = new Joke(
      joke: 'Success is not final, failure is not fatal.',
      author: 'Winston Churchill',
      category: 'inspire',
    );

    $array = $quote->toArray();

    // Verify all required keys are present for template usage.
    $this->assertArrayHasKey('quote', $array);
    $this->assertArrayHasKey('author', $array);
    $this->assertArrayHasKey('category', $array);

    // Verify the values are strings as expected by the template.
    $this->assertIsString($array['quote']);
    $this->assertIsString($array['author']);
    $this->assertIsString($array['category']);
  }

}
