# BoxUK Quotes Module

A Drupal module that provides a service to fetch inspirational quotes of the day from the [quotes.rest API](https://quotes.rest/) with a reusable Twig component.

## Features

- **Value Object Pattern**: Immutable `Quote` value object for type-safe quote representation
- **API Client Service**: Clean service interface for fetching quotes from quotes.rest
- **Pipeline Integration**: `QuoteOfTheDayPipe` for injecting quote data into template context
- **Reusable Twig Component**: BEM-based, semantic HTML template for displaying quotes
- **Comprehensive Tests**: 14 unit tests with 35 assertions

## Installation

1. Enable the module:
   ```bash
   ddev drush en boxuk_quotes -y
   ```

2. Clear cache:
   ```bash
   ddev drush cr
   ```

## Usage

### Using the API Client Service Directly

```php
$apiClient = \Drupal::service('boxuk_quotes.api_client');
$quote = $apiClient->getQuoteOfTheDay();

if ($quote) {
  $quoteText = $quote->getQuote();
  $author = $quote->getAuthor();
  $category = $quote->getCategory();
}
```

### Using with Pipeline Pattern

Add the `QuoteOfTheDayPipe` to any entity's pipeline:

```php
use Drupal\boxuk_quotes\Pipe\QuoteOfTheDayPipe;
use Drupal\boxuk_patterns\Pipe\Pipeline;

public function getStyleData(): array {
  return Pipeline::create()
    ->send($this)
    ->through([
      QuoteOfTheDayPipe::class,
      // ... other pipes
    ])
    ->thenReturn();
}
```

### Using the Twig Template

In your Twig template:

```twig
{# Basic usage #}
{% if style_data.quote_of_the_day %}
  {% include '@boxuk_quotes/quote-of-the-day.html.twig' with {
    'quote': style_data.quote_of_the_day.quote,
    'author': style_data.quote_of_the_day.author
  } %}
{% endif %}

{# With category displayed #}
{% include '@boxuk_quotes/quote-of-the-day.html.twig' with {
  'quote': style_data.quote_of_the_day.quote,
  'author': style_data.quote_of_the_day.author,
  'category': style_data.quote_of_the_day.category,
  'show_category': true
} %}

{# Inline format with custom classes #}
{% include '@boxuk_quotes/quote-of-the-day.html.twig' with {
  'quote': style_data.quote_of_the_day.quote,
  'author': style_data.quote_of_the_day.author,
  'format': 'inline',
  'classes': ['my-custom-class']
} %}
```

## Architecture

### Value Object: `Quote`

Immutable value object representing a quote:

```php
$quote = new Quote(
  quote: 'The only way to do great work is to love what you do.',
  author: 'Steve Jobs',
  category: 'inspire'
);

$quote->getQuote();     // Returns the quote text
$quote->getAuthor();    // Returns the author
$quote->getCategory();  // Returns the category
$quote->toArray();      // Returns array for template usage
```

### Service: `QuotesApiClient`

Fetches quotes from the quotes.rest API:

- Endpoint: `https://quotes.rest/qod.json?category=inspire`
- Returns `Quote` object on success, `null` on error
- Validates API response structure
- Handles network errors gracefully
- TODO: Add error logging for production use

### Pipe: `QuoteOfTheDayPipe`

Integrates with the Pipeline pattern from `boxuk_patterns`:

- Fetches quote from API client service
- Returns data in array structure for template usage
- Returns empty array on error (fail silently)
- Independent of entity data (doesn't require specific entity type)

### Template: `quote-of-the-day.html.twig`

Reusable Twig component with:

- BEM CSS naming convention
- Semantic HTML (`<blockquote>`, `<cite>`)
- ARIA labels for accessibility
- Configurable display format (block/inline)
- Optional category display
- Graceful handling of missing data

## Testing

The module includes comprehensive unit tests:

```bash
# Run all boxuk_quotes tests
ddev exec "vendor/bin/phpunit -c web/phpunit.xml web/modules/boxuk_quotes/tests/src/Unit/"

# Run specific test class
ddev exec "vendor/bin/phpunit -c web/phpunit.xml web/modules/boxuk_quotes/tests/src/Unit/ValueObject/QuoteTest.php"
```

**Test Coverage:**
- `QuoteTest`: 5 tests - Value object creation, validation, immutability
- `QuotesApiClientTest`: 6 tests - API client with mocked HTTP responses
- `QuoteOfTheDayPipeTest`: 3 tests - Pipe behavior and structure

**Total: 14 tests, 35 assertions**

## API Rate Limiting

The quotes.rest API has a free tier rate limit of 10 calls per hour. For production use, consider:

- Implementing caching (24-hour cache for "quote of the day")
- Using Drupal's Cache API
- Optionally configuring an API key for higher limits

## Future Enhancements

- [ ] Add error logging (see TODO comments in code)
- [ ] Implement caching strategy for API responses
- [ ] Add configuration form for API key
- [ ] Support for multiple quote categories
- [ ] Add block plugin for easy placement in regions
- [ ] Create Drush command for testing API connectivity

## Design Patterns

This module demonstrates several design patterns:

1. **Value Object Pattern**: Immutable `Quote` class
2. **Service Pattern**: `QuotesApiClient` for API communication
3. **Pipeline Pattern**: Integration with `boxuk_patterns` pipeline
4. **Dependency Injection**: Service registered in `services.yml`
5. **Component-Based Templates**: Reusable Twig component

## Dependencies

- Drupal 10
- `boxuk_patterns` module (for Pipeline pattern integration)
- GuzzleHttp (provided by Drupal core)

## License

This module is part of the BoxUK Drupal coding exercise template.

## Author

Created as part of the BoxUK interview coding exercise.
