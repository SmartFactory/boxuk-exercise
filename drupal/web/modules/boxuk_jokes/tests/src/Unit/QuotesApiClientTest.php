<?php

declare(strict_types=1);

namespace Drupal\Tests\boxuk_jokes\Unit;

use Drupal\boxuk_jokes\JokeApiClient;
use Drupal\boxuk_jokes\ValueObject\Joke;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Tests for the JokesApiClient service.
 *
 * @group boxuk_jokes
 * @coversDefaultClass \Drupal\boxuk_jokes\JokeApiClient
 */
class JokesApiClientTest extends UnitTestCase {

  /**
   * Test successful API response returns Joke object.
   *
   * @covers ::__construct
   * @covers ::getJokeOfTheDay
   * @covers ::isValidResponse
   */
  public function testGetJokeOfTheDayReturnsJoke(): void {
    // Create mock response data.
    $responseData = [
      'contents' => [
        'jokes' => [
          [
            'joke' => 'The only way to do great work is to love what you do.',
            'author' => 'Steve Jobs',
            'category' => 'inspire',
          ],
        ],
      ],
    ];

    // Mock the HTTP client and response.
    $httpClient = $this->createMockHttpClient($responseData);

    // Create the API client and fetch joke.
    $apiClient = new JokeApiClient($httpClient);
    $joke = $apiClient->getJokeOfTheDay();

    // Assert we got a Joke object with correct data.
    $this->assertInstanceOf(Joke::class, $joke);
    $this->assertEquals('The only way to do great work is to love what you do.', $joke->getJoke());
    $this->assertEquals('Steve Jobs', $joke->getAuthor());
    $this->assertEquals('inspire', $joke->getCategory());
  }

  /**
   * Test API response without category uses default.
   *
   * @covers ::getJokeOfTheDay
   */
  public function testGetJokeOfTheDayWithoutCategory(): void {
    $responseData = [
      'contents' => [
        'jokes' => [
          [
            'joke' => 'Test joke',
            'author' => 'Test Author',
          ],
        ],
      ],
    ];

    $httpClient = $this->createMockHttpClient($responseData);
    $apiClient = new JokeApiClient($httpClient);
    $joke = $apiClient->getJokeOfTheDay();

    $this->assertInstanceOf(Joke::class, $joke);
    $this->assertEquals('inspire', $joke->getCategory());
  }

  /**
   * Test invalid API response returns null.
   *
   * @covers ::getJokeOfTheDay
   * @covers ::isValidResponse
   */
  public function testGetJokeOfTheDayWithInvalidResponseReturnsNull(): void {
    // Invalid response structure (missing required fields).
    $responseData = [
      'contents' => [
        'jokes' => [
          ['invalid' => 'data'],
        ],
      ],
    ];

    $httpClient = $this->createMockHttpClient($responseData);
    $apiClient = new JokeApiClient($httpClient);
    $joke = $apiClient->getJokeOfTheDay();

    $this->assertNull($joke);
  }

  /**
   * Test empty response returns null.
   *
   * @covers ::getJokeOfTheDay
   * @covers ::isValidResponse
   */
  public function testGetJokeOfTheDayWithEmptyResponseReturnsNull(): void {
    $httpClient = $this->createMockHttpClient([]);
    $apiClient = new JokeApiClient($httpClient);
    $joke = $apiClient->getJokeOfTheDay();

    $this->assertNull($joke);
  }

  /**
   * Test API request exception returns null.
   *
   * @covers ::getJokeOfTheDay
   */
  public function testGetJokeOfTheDayWithExceptionReturnsNull(): void {
    // Create mock HTTP client that throws exception.
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->method('request')
      ->willThrowException($this->createMock(RequestException::class));

    $apiClient = new JokeApiClient($httpClient);
    $joke = $apiClient->getJokeOfTheDay();

    $this->assertNull($joke);
  }

  /**
   * Test response with empty joke text returns null.
   *
   * @covers ::getJokeOfTheDay
   */
  public function testGetJokeOfTheDayWithEmptyJokeTextReturnsNull(): void {
    $responseData = [
      'contents' => [
        'jokes' => [
          [
            'joke' => '',
            'author' => 'Someone',
            'category' => 'inspire',
          ],
        ],
      ],
    ];

    $httpClient = $this->createMockHttpClient($responseData);
    $apiClient = new JokeApiClient($httpClient);
    $joke = $apiClient->getJokeOfTheDay();

    // Should return null because Joke constructor validates non-empty.
    $this->assertNull($joke);
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
