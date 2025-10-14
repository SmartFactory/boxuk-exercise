<?php

namespace Drupal\boxuk_quotes\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\boxuk_quotes\JokeApiClient;

/**
 * Pipe to fetch and inject joke of the day data into template context.
 *
 * This pipe fetches a random joke from the icanhazdadjoke.com API and makes
 * it available in the template variables. It can be used in any entity's
 * pipeline to inject joke data into the template context.
 *
 * The object passed to this pipe is not used, as the joke data comes from
 * an external API rather than entity properties.
 *
 * Example usage in template:
 * @code
 * {% if style_data.jod %}
 *   {% include '@boxuk_quotes/joke-of-the-day.html.twig' with style_data.jod %}
 * {% endif %}
 * @endcode
 */
final class JokeOfTheDayPipe extends BasePipe {

  /**
   * The quotes API client service.
   *
   * @var \Drupal\boxuk_quotes\JokeApiClient
   */
  private JokeApiClient $apiClient;

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
    // Fetch the joke from the API.
    $joke = $this->apiClient->getJokeOfTheDay();

    // Return empty array if no joke was retrieved.
    if ($joke === NULL) {
      return [];
    }

    // Convert the joke value object to array for template usage.
    return [
      'jod' => $joke->toArray(),
    ];
  }

}
