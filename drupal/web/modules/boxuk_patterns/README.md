# BoxUK Patterns

A Drupal module demonstrating design patterns for clean, testable, and maintainable bundle classes.

## What's This?

This module showcases **two design patterns** that work together to improve code quality in Drupal:

1. **Pipeline Pattern** - Flexible, extensible data processing for templates
2. **Facade Pattern** - Simplified API for accessing entity data

## Quick Example

### Pipeline Pattern

```php
// In bundle class
public function getStyleData(): array {
  return Pipeline::create()
    ->send($this)
    ->through([HeaderPipe::class, TitlePipe::class])
    ->thenReturn();
}
```

```twig
{# In template #}
{% set style_data = node.getStyleData() %}
{{ style_data.header }}
{{ style_data.title.formatted }}
```

### Facade Pattern

```php
// Clean methods instead of Field API
$subtitle = $article->getSubtitle();
$readingTime = $article->getReadingTime();
$isFeatured = $article->isFeatured();

// Fluent setters
$article
  ->setSubtitle('New subtitle')
  ->setFeatured(TRUE)
  ->save();
```

```twig
{# In templates #}
<h2>{{ node.getSubtitle() }}</h2>
<span>{{ node.getReadingTime() }} min read</span>

{% if node.isFeatured() %}
  <span class="badge">Featured</span>
{% endif %}
```

## Why Use This?

### Before (Complex)

```php
// Field API is verbose
if ($node->hasField('field_subtitle')) {
  $subtitle = $node->get('field_subtitle')->value;
}

// Calculations scattered everywhere
$body = $node->get('body')->value;
$wordCount = str_word_count(strip_tags($body));
$readingTime = ceil($wordCount / 200);
```

### After (Clean)

```php
// Simple, intuitive methods
$subtitle = $article->getSubtitle();
$readingTime = $article->getReadingTime();
```

## Features

- ✅ **Clean API** - Simple methods instead of complex Field API
- ✅ **Extensible** - Easy to add new pipes and methods
- ✅ **Testable** - Services can be unit tested
- ✅ **Type-Safe** - Proper type hints and return types
- ✅ **Developer-Friendly** - Clear separation of framework vs application code
- ✅ **Well-Documented** - Comprehensive guides and examples

## Getting Started

### 1. Create a New Pipe

```php
// src/StyleData/Pipe/CategoryPipe.php
namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

class CategoryPipe extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    // Your logic here
    return ['categories' => $this->getCategories($object)];
  }
}
```

### 2. Register the Pipe

```php
// src/Entity/Node/BoxUkArticle.php
public function getStyleData(): array {
  return Pipeline::create()
    ->send($this)
    ->through([
      HeaderPipe::class,
      CategoryPipe::class,  // Add your pipe
    ])
    ->thenReturn();
}
```

### 3. Use in Template

```twig
{% set style_data = node.getStyleData() %}

{% for category in style_data.categories %}
  <span>{{ category }}</span>
{% endfor %}
```

## Documentation

### 📚 Complete Guides

- **[Getting Started](docs/01-getting-started.md)** - Quick start guide, directory structure, common patterns
- **[Design Patterns](docs/02-patterns.md)** - Pipeline & Facade patterns explained with examples
- **[Code Examples](docs/03-examples.md)** - 10+ real-world pipe examples
- **[API Reference](docs/04-api-reference.md)** - Complete method reference

### 🎯 Quick Links

| I want to... | Go to |
|--------------|-------|
| Get started quickly | [Getting Started](docs/01-getting-started.md#quick-start-create-your-first-pipe) |
| Understand the patterns | [Design Patterns](docs/02-patterns.md) |
| See real examples | [Code Examples](docs/03-examples.md) |
| Look up a method | [API Reference](docs/04-api-reference.md) |
| Know where to create pipes | [Getting Started - Directory Structure](docs/01-getting-started.md#understanding-the-directory-structure) |

## Installation

1. Enable the module:
   ```bash
   drush en boxuk_patterns
   ```

2. Clear cache:
   ```bash
   drush cr
   ```

## Directory Structure

```
boxuk_patterns/
├── README.md                          # This file
├── docs/                              # 📚 Documentation
│   ├── 01-getting-started.md
│   ├── 02-patterns.md
│   ├── 03-examples.md
│   └── 04-api-reference.md
│
├── src/
│   ├── Pipe/                          # ⚙️ Framework (don't modify)
│   │   ├── Contract/
│   │   ├── BasePipe.php
│   │   ├── BasePipeline.php
│   │   └── Pipeline.php
│   │
│   ├── StyleData/Pipe/                # 👨‍💻 Your pipes go here!
│   │   ├── HeaderPipe.php
│   │   ├── TitlePipe.php
│   │   ├── AuthorPipe.php
│   │   └── DatePipe.php
│   │
│   ├── ArticleFormatter.php           # Facade service
│   │
│   └── Entity/Node/
│       └── BoxUkArticle.php           # Bundle class
│
└── templates/
    └── node--article.html.twig        # Example template
```

## Available Methods

### Pipeline

```php
$styleData = $article->getStyleData();
// Returns complex nested array from all pipes
```

### Facade (Getters)

```php
$article->getSubtitle();                // string|null
$article->getSummary(150);              // string|null
$article->getReadingTime();             // int
$article->getWordCount();               // int
$article->isFeatured();                 // bool
$article->getFeaturedImageUrl('large'); // string|null
$article->getPublishedDate('Y-m-d');    // string
$article->getAuthorName();              // string
$article->isRecent(7);                  // bool
$article->getTags();                    // array
$article->getCategories();              // array
```

### Facade (Setters)

```php
$article->setSubtitle('text');    // Fluent interface
$article->setFeatured(TRUE);      // Fluent interface
```

## Benefits

### Clean Code

**Before:**
```php
if ($node->hasField('body')) {
  $body = $node->get('body')->value;
  $wordCount = str_word_count(strip_tags($body));
  $readingTime = ceil($wordCount / 200);
}
```

**After:**
```php
$readingTime = $article->getReadingTime();
```

### Extensible

Add a new pipe in 3 steps:
1. Create pipe in `src/StyleData/Pipe/`
2. Register in `BoxUkArticle::getStyleData()`
3. Use in template

### Testable

```php
// Test pipes in isolation
$pipe = new TitlePipe($mockNode);
$result = $pipe->handle();
$this->assertEquals('Title', $result['title']['text']);

// Test service methods
$formatter = new ArticleFormatter();
$subtitle = $formatter->getSubtitle($mockNode);
$this->assertEquals('Subtitle', $subtitle);
```

## Requirements

- Drupal 10+
- PHP 8.1+

## Support

For issues, questions, or contributions, please see the module documentation in the `docs/` directory.

## License

GPL-2.0+

## Credits

Developed by BoxUK as a demonstration of design patterns in Drupal.
