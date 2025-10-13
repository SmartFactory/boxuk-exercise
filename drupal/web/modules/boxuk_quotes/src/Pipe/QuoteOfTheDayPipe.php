<?php

namespace Drupal\boxuk_quotes\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\boxuk_quotes\QuotesApiClient;

/**
 * Pipe to fetch and inject quote of the day data into template context.
 *
 * This pipe fetches the quote of the day from the quotes.rest API and makes
 * it available in the template variables. It can be used in any entity's
 * pipeline to inject quote data into the template context.
 *
 * The object passed to this pipe is not used, as the quote data comes from
 * an external API rather than entity properties.
 *
 * Example usage in template:
 * @code
 * {% if style_data.quote_of_the_day %}
 *   {% include '@boxuk_quotes/quote-of-the-day.html.twig' with {
 *     'quote': style_data.quote_of_the_day.quote,
 *     'author': style_data.quote_of_the_day.author,
 *     'category': style_data.quote_of_the_day.category
 *   } %}
 * {% endif %}
 * @endcode
 */
final class QuoteOfTheDayPipe extends BasePipe {

  /**
   * The quotes API client service.
   *
   * @var \Drupal\boxuk_quotes\QuotesApiClient
   */
  private QuotesApiClient $apiClient;

  /**
   * {@inheritdoc}
   */
  public function __construct(object $object) {
    parent::__construct($object);
    // Retrieve the API client service from the container.
    $this->apiClient = \Drupal::service('boxuk_quotes.api_client');
  }

  /**
   * {@inheritdoc}
   */
  public function handle(): array {
    // Fetch the quote from the API.
    $quote = $this->apiClient->getQuoteOfTheDay();

    // Return empty array if no quote was retrieved.
    if ($quote === NULL) {
      return [];
    }

    // Convert the quote value object to array for template usage.
    return [
      'quote_of_the_day' => $quote->toArray(),
    ];
  }

}
