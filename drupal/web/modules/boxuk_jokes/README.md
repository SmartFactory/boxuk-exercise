# BoxUK Jokes Module

A Drupal module that provides a service to fetch jokes with a reusable Twig component.

## Features

- **Value Object Pattern**: Immutable `Joke` value object for type-safe joke representation
- **API Client Service**: Clean service interface for fetching jokes from icanhazdadjoke.com
- **Pipeline Integration**: `JokeOfTheDayPipe` for injecting joke data into template context
- **Reusable Twig Component**: BEM-based, semantic HTML template for displaying jokes
- **Comprehensive Tests**: 14 unit tests with 35 assertions

## Installation

1. Enable the module:
   ```bash
   ddev drush en boxuk_jokes -y
   ```

2. Clear cache:
   ```bash
   ddev drush cr
   ```

## Usage

### Using the API Client Service Directly

```php
$apiClient = \Drupal::service('boxuk_jokes.api_client');
$joke = $apiClient->getJokeOfTheDay();

if ($joke) {
  $jokeText = $joke->getJoke();
  $author = $joke->getAuthor();
  $category = $joke->getCategory();
}
```

### Using with Pipeline Pattern

Add the `JokeOfTheDayPipe` to any entity's pipeline:

```php
use Drupal\boxuk_jokes\Pipe\JokeOfTheDayPipe;
use Drupal\boxuk_patterns\Pipe\Pipeline;

public function getStyleData(): array {
  return Pipeline::create()
    ->send($this)
    ->through([
      JokeOfTheDayPipe::class,
      // ... other pipes
    ])
    ->thenReturn();
}
```

### Using the Twig Template

In your Twig template:

```twig
{# Basic usage #}
{% if style_data.jod %}
  {% include '@boxuk_jokes/joke-of-the-day.html.twig' with {
    'joke': style_data.jod.joke,
    'author': style_data.jod.author
  } %}
{% endif %}

{# With category displayed #}
{% include '@boxuk_jokes/joke-of-the-day.html.twig' with {
  'joke': style_data.joke_of_the_day.joke,
  'author': style_data.joke_of_the_day.author,
  'category': style_data.joke_of_the_day.category,
  'show_category': true
} %}

{# Inline format with custom classes #}
{% include '@boxuk_jokes/joke-of-the-day.html.twig' with {
  'joke': style_data.joke_of_the_day.joke,
  'author': style_data.joke_of_the_day.author,
  'format': 'inline',
  'classes': ['my-custom-class']
} %}
```

## Architecture

### Value Object: `Joke`

Immutable value object representing a joke:

```php
$joke = new Joke(
  joke: 'The only way to do great work is to love what you do.',
  author: 'Steve Jobs',
  category: 'inspire'
);

$joke->getJoke();     // Returns the joke text
$joke->getAuthor();    // Returns the author
$joke->getCategory();  // Returns the category
$joke->toArray();      // Returns array for template usage
```
