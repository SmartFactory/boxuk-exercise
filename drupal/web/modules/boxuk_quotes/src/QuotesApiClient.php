<?php

namespace Drupal\boxuk_quotes;

use Drupal\boxuk_quotes\ValueObject\Quote;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Service for fetching quotes from the quotes.rest API.
 *
 * This service provides a simple interface for retrieving the quote of the day
 * from the quotes.rest API. It handles API communication, response parsing,
 * and error handling.
 */
final class QuotesApiClient {

  /**
   * The quotes.rest API endpoint for quote of the day.
   */
  private const API_ENDPOINT = 'https://quotes.rest/qod.json';

  /**
   * The category for inspirational quotes.
   */
  private const CATEGORY_INSPIRE = 'inspire';

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
   * Fetches the quote of the day from the API.
   *
   * @return \Drupal\boxuk_quotes\ValueObject\Quote|null
   *   The Quote value object if successful, NULL on error.
   */
  public function getQuoteOfTheDay(): ?Quote {
    try {
      $response = $this->httpClient->request('GET', self::API_ENDPOINT, [
        'query' => ['category' => self::CATEGORY_INSPIRE],
      ]);

      $data = json_decode($response->getBody()->getContents(), TRUE);

      // Validate response structure.
      if (!$this->isValidResponse($data)) {
        // TODO: Add error logging for invalid API responses.
        return NULL;
      }

      $quoteData = $data['contents']['quotes'][0];

      return new Quote(
        quote: $quoteData['quote'],
        author: $quoteData['author'],
        category: $quoteData['category'] ?? self::CATEGORY_INSPIRE,
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
      && isset($data['contents']['quotes'][0]['quote'])
      && isset($data['contents']['quotes'][0]['author'])
      && is_string($data['contents']['quotes'][0]['quote'])
      && is_string($data['contents']['quotes'][0]['author']);
  }

}
