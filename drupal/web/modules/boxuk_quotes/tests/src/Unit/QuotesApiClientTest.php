<?php

declare(strict_types=1);

namespace Drupal\Tests\boxuk_quotes\Unit;

use Drupal\boxuk_quotes\JokeApiClient;
use Drupal\boxuk_quotes\ValueObject\Joke;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Tests for the QuotesApiClient service.
 *
 * @group boxuk_quotes
 * @coversDefaultClass \Drupal\boxuk_quotes\JokeApiClient
 */
class QuotesApiClientTest extends UnitTestCase {

  /**
   * Test successful API response returns Quote object.
   *
   * @covers ::__construct
   * @covers ::getQuoteOfTheDay
   * @covers ::isValidResponse
   */
  public function testGetQuoteOfTheDayReturnsQuote(): void {
    // Create mock response data.
    $responseData = [
      'contents' => [
        'quotes' => [
          [
            'quote' => 'The only way to do great work is to love what you do.',
            'author' => 'Steve Jobs',
            'category' => 'inspire',
          ],
        ],
      ],
    ];

    // Mock the HTTP client and response.
    $httpClient = $this->createMockHttpClient($responseData);

    // Create the API client and fetch quote.
    $apiClient = new JokeApiClient($httpClient);
    $quote = $apiClient->getQuoteOfTheDay();

    // Assert we got a Quote object with correct data.
    $this->assertInstanceOf(Joke::class, $quote);
    $this->assertEquals('The only way to do great work is to love what you do.', $quote->getJoke());
    $this->assertEquals('Steve Jobs', $quote->getAuthor());
    $this->assertEquals('inspire', $quote->getCategory());
  }

  /**
   * Test API response without category uses default.
   *
   * @covers ::getQuoteOfTheDay
   */
  public function testGetQuoteOfTheDayWithoutCategory(): void {
    $responseData = [
      'contents' => [
        'quotes' => [
          [
            'quote' => 'Test quote',
            'author' => 'Test Author',
          ],
        ],
      ],
    ];

    $httpClient = $this->createMockHttpClient($responseData);
    $apiClient = new JokeApiClient($httpClient);
    $quote = $apiClient->getQuoteOfTheDay();

    $this->assertInstanceOf(Joke::class, $quote);
    $this->assertEquals('inspire', $quote->getCategory());
  }

  /**
   * Test invalid API response returns null.
   *
   * @covers ::getQuoteOfTheDay
   * @covers ::isValidResponse
   */
  public function testGetQuoteOfTheDayWithInvalidResponseReturnsNull(): void {
    // Invalid response structure (missing required fields).
    $responseData = [
      'contents' => [
        'quotes' => [
          ['invalid' => 'data'],
        ],
      ],
    ];

    $httpClient = $this->createMockHttpClient($responseData);
    $apiClient = new JokeApiClient($httpClient);
    $quote = $apiClient->getQuoteOfTheDay();

    $this->assertNull($quote);
  }

  /**
   * Test empty response returns null.
   *
   * @covers ::getQuoteOfTheDay
   * @covers ::isValidResponse
   */
  public function testGetQuoteOfTheDayWithEmptyResponseReturnsNull(): void {
    $httpClient = $this->createMockHttpClient([]);
    $apiClient = new JokeApiClient($httpClient);
    $quote = $apiClient->getQuoteOfTheDay();

    $this->assertNull($quote);
  }

  /**
   * Test API request exception returns null.
   *
   * @covers ::getQuoteOfTheDay
   */
  public function testGetQuoteOfTheDayWithExceptionReturnsNull(): void {
    // Create mock HTTP client that throws exception.
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->method('request')
      ->willThrowException($this->createMock(RequestException::class));

    $apiClient = new JokeApiClient($httpClient);
    $quote = $apiClient->getQuoteOfTheDay();

    $this->assertNull($quote);
  }

  /**
   * Test response with empty quote text returns null.
   *
   * @covers ::getQuoteOfTheDay
   */
  public function testGetQuoteOfTheDayWithEmptyQuoteTextReturnsNull(): void {
    $responseData = [
      'contents' => [
        'quotes' => [
          [
            'quote' => '',
            'author' => 'Someone',
            'category' => 'inspire',
          ],
        ],
      ],
    ];

    $httpClient = $this->createMockHttpClient($responseData);
    $apiClient = new JokeApiClient($httpClient);
    $quote = $apiClient->getQuoteOfTheDay();

    // Should return null because Quote constructor validates non-empty.
    $this->assertNull($quote);
  }

  /**
   * Helper method to create a mock HTTP client with a response.
   *
   * @param array $responseData
   *   The data to return in the response.
   *
   * @return \GuzzleHttp\ClientInterface
   *   A mock HTTP client.
   */
  private function createMockHttpClient(array $responseData): ClientInterface {
    // Mock the response body stream.
    $stream = $this->createMock(StreamInterface::class);
    $stream->method('getContents')
      ->willReturn(json_encode($responseData));

    // Mock the response.
    $response = $this->createMock(ResponseInterface::class);
    $response->method('getBody')
      ->willReturn($stream);

    // Mock the HTTP client.
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->method('request')
      ->willReturn($response);

    return $httpClient;
  }

}
