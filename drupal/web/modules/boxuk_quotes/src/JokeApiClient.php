<?php

namespace Drupal\boxuk_quotes;

use Drupal\boxuk_quotes\ValueObject\Joke;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Service for fetching jokes from the icanhazdadjoke API.
 *
 * This service provides a simple interface for retrieving random dad jokes
 * from the icanhazdadjoke.com API. It handles API communication, response parsing,
 * and error handling.
 */
final class JokeApiClient {

  /**
   * The icanhazdadjoke API endpoint for random jokes.
   */
  private const API_ENDPOINT = 'https://icanhazdadjoke.com/';

  /**
   * Constructs a QuotesApiClient.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client service.
   */
  public function __construct(
    private readonly ClientInterface $httpClient,
  ) {}

  /**
   * Fetches a random joke from the API.
   *
   * @return \Drupal\boxuk_quotes\ValueObject\Joke|null
   *   The Quote value object containing the joke, NULL on error.
   */
  public function getJokeOfTheDay(): ?Joke {
    try {
      $response = $this->httpClient->request('GET', self::API_ENDPOINT, [
        'headers' => [
          'Accept' => 'application/json',
          'User-Agent' => 'Drupal BoxUK Quotes Module (https://github.com/boxuk)',
        ],
        'timeout' => 10,
        'connect_timeout' => 5,
      ]);

      $data = json_decode($response->getBody()->getContents(), TRUE);

      // Validate response structure.
      if (!$this->isValidResponse($data)) {
        // TODO: Add error logging for invalid API responses.
        return NULL;
      }

      return new Joke(
        joke: $data['joke'],
        author: 'icanhazdadjoke.com',
        category: 'humor',
      );
    }
    catch (GuzzleException $e) {
      // TODO: Add error logging for API request failures.
      return NULL;
    }
    catch (\InvalidArgumentException $e) {
      // Quote value object validation failed.
      // TODO: Add error logging for invalid quote data.
      return NULL;
    }
  }

  /**
   * Validates the API response structure.
   *
   * @param mixed $data
   *   The decoded JSON response data.
   *
   * @return bool
   *   TRUE if the response has the expected structure, FALSE otherwise.
   */
  private function isValidResponse(mixed $data): bool {
    return is_array($data)
      && isset($data['joke'])
      && isset($data['status'])
      && $data['status'] === 200
      && !empty($data['joke']);
  }

}
