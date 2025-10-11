# Design Patterns

This module demonstrates two complementary design patterns that work together to create clean, maintainable code.

## Table of Contents

- [Overview](#overview)
- [Pipeline Pattern](#pipeline-pattern)
- [Facade Pattern](#facade-pattern)
- [How They Work Together](#how-they-work-together)
- [Architecture](#architecture)

---

## Overview

### The Two Patterns

| Pattern | Purpose | Use Case |
|---------|---------|----------|
| **Pipeline** | Flexible, extensible data processing | Aggregate data from multiple sources for templates |
| **Facade** | Simplified API for common operations | Clean methods instead of complex Field API |

### When to Use Each

**Use Pipeline Pattern When:**
- ✅ You need to aggregate data from **multiple sources**
- ✅ The data structure is **complex** (nested arrays)
- ✅ The data is primarily for **template rendering**
- ✅ You want **extensibility** (easy to add new pipes)

**Use Facade Pattern When:**
- ✅ You need **simple, direct access** to specific data
- ✅ The data is a **single value** or simple array
- ✅ You want a **clean API** for common operations
- ✅ You need **type safety** and clear contracts

---

## Pipeline Pattern

### What Is It?

The Pipeline Pattern allows you to pass an object through a series of operations (pipes), where each pipe adds or transforms data. Results are automatically merged into a single array.

### How It Works

```
Node Entity → Pipeline → [Pipe1, Pipe2, Pipe3] → Merged Array → Template
```

### Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     BoxUkArticle::getStyleData()             │
├─────────────────────────────────────────────────────────────┤
│  Pipeline::create()                                          │
│    ->send($this)         // Pass the node entity             │
│    ->through([           // Process through pipes            │
│        HeaderPipe::class,                                    │
│        TitlePipe::class,                                     │
│        AuthorPipe::class,                                    │
│    ])                                                        │
│    ->thenReturn()        // Return merged array             │
└────────┬────────────────────────────────────────────────────┘
         │
         ├─────────────┬───────────────┬──────────────┐
         ▼             ▼               ▼              ▼
    HeaderPipe    TitlePipe     AuthorPipe      DatePipe
         │             │               │              │
         ▼             ▼               ▼              ▼
    ['header':]  ['title':]    ['author':]    ['dates':]
                      │               │              │
                      └───────┬───────┴──────────────┘
                              ▼
                      Deep Array Merge
                              ▼
                    ┌─────────────────┐
                    │  Merged Result  │
                    │  {              │
                    │    header: '...'│
                    │    title: {...} │
                    │    author: {...}│
                    │    dates: {...} │
                    │  }              │
                    └─────────────────┘
```

### Example Usage

```php
// In BoxUkArticle.php
public function getStyleData(): array {
  return Pipeline::create()
    ->send($this)
    ->through([
      HeaderPipe::class,
      TitlePipe::class,
      AuthorPipe::class,
    ])
    ->thenReturn();
}

// Returns:
[
  'header' => 'Hello World',
  'title' => ['text' => '...', 'formatted' => '...'],
  'author' => ['name' => '...', 'id' => '...'],
]
```

### In Templates

```twig
{% set style_data = node.getStyleData() %}

{{ style_data.header }}
{{ style_data.title.formatted }}
{{ style_data.author.name }}
```

### Creating a Pipe

```php
namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

class CategoryPipe extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    if (!$object->hasField('field_categories')) {
      return [];
    }

    $categories = [];
    foreach ($object->get('field_categories') as $item) {
      $categories[] = $item->entity->getName();
    }

    return ['categories' => $categories];
  }

}
```

### Benefits

- ✅ **Separation of Concerns** - Each pipe handles one transformation
- ✅ **Testability** - Pipes are easy to unit test in isolation
- ✅ **Reusability** - Pipes can be reused across different bundle classes
- ✅ **Maintainability** - Easy to add/remove/modify data sources
- ✅ **Extensibility** - Developers can add new pipes without modifying existing code

---

## Facade Pattern

### What Is It?

The Facade Pattern provides a simplified interface to a complex subsystem. In our case, it hides Drupal's Field API behind clean, intuitive methods.

### The Problem

Without the facade, accessing article data requires verbose Field API calls:

```php
// ❌ Complex and error-prone
if ($node->hasField('field_subtitle')) {
  $subtitle = $node->get('field_subtitle')->value;
}

$body = $node->get('body')->value;
$wordCount = str_word_count(strip_tags($body));
$readingTime = ceil($wordCount / 200);
```

### The Solution

```php
// ✅ Clean and simple
$subtitle = $article->getSubtitle();
$readingTime = $article->getReadingTime();
```

### Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                   BoxUkArticle (Facade)                      │
├─────────────────────────────────────────────────────────────┤
│  - getSubtitle()         →  formatter()->getSubtitle($this) │
│  - getSummary()          →  formatter()->getSummary($this)  │
│  - getReadingTime()      →  formatter()->getReadingTime()   │
│  - isFeatured()          →  formatter()->isFeatured($this)  │
│  - getTags()             →  formatter()->getTags($this)     │
│                                                              │
│  Simple API for developers                                  │
└──────────────────┬───────────────────────────────────────────┘
                   │ delegates to
                   ▼
         ┌──────────────────────┐
         │  ArticleFormatter    │
         │     (Service)        │
         ├──────────────────────┤
         │  • Field access      │
         │  • Business logic    │
         │  • Calculations      │
         │  • Formatting        │
         │                      │
         │  Testable, reusable  │
         └──────────────────────┘
```

### Example Usage

#### In Templates

```twig
<h2>{{ node.getSubtitle() }}</h2>
<p>{{ node.getSummary(150) }}</p>
<span>{{ node.getReadingTime() }} min read</span>

{% if node.isFeatured() %}
  <span class="badge">Featured</span>
{% endif %}
```

#### In Code

```php
// Get values
$subtitle = $article->getSubtitle();
$summary = $article->getSummary(150);
$tags = $article->getTags();

// Set values (fluent interface)
$article
  ->setSubtitle('New subtitle')
  ->setFeatured(TRUE)
  ->save();

// Check conditions
if ($article->isRecent(7)) {
  // Recent article logic
}
```

### Implementation

#### 1. Service Layer (ArticleFormatter)

```php
final class ArticleFormatter {

  public function getSubtitle(NodeInterface $node): ?string {
    if (!$node->hasField('field_subtitle')) {
      return NULL;
    }
    return $node->get('field_subtitle')->value;
  }

  public function getReadingTime(NodeInterface $node, int $wpm = 200): int {
    if (!$node->hasField('body')) {
      return 0;
    }

    $body = $node->get('body')->value;
    $wordCount = str_word_count(strip_tags($body));

    return (int) ceil($wordCount / $wpm);
  }
}
```

#### 2. Facade Layer (BoxUkArticle)

```php
final class BoxUkArticle extends Node {

  private function formatter(): ArticleFormatter {
    if (!$this->formatter) {
      $this->formatter = \Drupal::service('boxuk_patterns.article_formatter');
    }
    return $this->formatter;
  }

  public function getSubtitle(): ?string {
    return $this->formatter()->getSubtitle($this);
  }

  public function getReadingTime(int $wpm = 200): int {
    return $this->formatter()->getReadingTime($this, $wpm);
  }
}
```

### Benefits

- ✅ **Simplified API** - Clean methods vs complex Field API
- ✅ **Better Encapsulation** - Field names hidden from callers
- ✅ **Type Safety** - Proper type hints and return types
- ✅ **Testability** - Service can be unit tested
- ✅ **Reusability** - Service usable anywhere
- ✅ **Self-Documenting** - Clear, intuitive method names

---

## How They Work Together

Both patterns can be used together for maximum flexibility:

```twig
{# Get structured data from pipeline #}
{% set style_data = node.getStyleData() %}

{# Use facade for additional simple values #}
<article>
  <header>
    {# From pipeline #}
    <h1>{{ style_data.title.formatted }}</h1>

    {# From facade #}
    <h2>{{ node.getSubtitle() }}</h2>
    <span>{{ node.getReadingTime() }} min read</span>
  </header>

  <div class="meta">
    {# From pipeline #}
    <span>By {{ style_data.author.name }}</span>

    {# From facade #}
    {% if node.isFeatured() %}
      <span class="badge">Featured</span>
    {% endif %}
  </div>

  {{ content }}
</article>
```

### Comparison

| Aspect | Pipeline Pattern | Facade Pattern |
|--------|------------------|----------------|
| **Complexity** | Handles complex nested data | Provides simple values |
| **Extensibility** | Easy to add new pipes | Easy to add new methods |
| **Use Case** | Template rendering | General purpose |
| **Return Type** | Complex array structure | Simple types |
| **Testability** | Test pipes individually | Test service methods |

---

## Architecture

### Complete System Overview

```
┌────────────────────────────────────────────────────────────┐
│                          USER CODE                          │
│                  (Templates, Controllers, etc.)             │
└────────┬──────────────────────────────────┬───────────────┘
         │                                  │
         │ getStyleData()                   │ getSubtitle()
         │                                  │ getReadingTime()
         │                                  │ etc.
         ▼                                  ▼
┌─────────────────────────────────────────────────────────────┐
│                     BoxUkArticle (Entity)                    │
├──────────────────────────┬──────────────────────────────────┤
│   PIPELINE PATTERN       │        FACADE PATTERN            │
│                          │                                  │
│   getStyleData() {       │   getSubtitle() {                │
│     Pipeline::create()   │     formatter()->getSubtitle()   │
│       ->send($this)      │   }                              │
│       ->through([...])   │                                  │
│       ->thenReturn()     │   getReadingTime() {             │
│   }                      │     formatter()->getReadingTime()│
│                          │   }                              │
└──────────┬───────────────┴───────────────┬──────────────────┘
           │                               │
           ▼                               ▼
  ┌──────────────────┐          ┌──────────────────────┐
  │  Pipeline        │          │  ArticleFormatter    │
  │  Framework       │          │  Service             │
  │                  │          │                      │
  │  • BasePipe      │          │  • getSubtitle()     │
  │  • BasePipeline  │          │  • getSummary()      │
  │  • Pipeline      │          │  • getReadingTime()  │
  └────────┬─────────┘          │  • isFeatured()      │
           │                    └──────────────────────┘
           ▼
  ┌──────────────────┐
  │  Custom Pipes    │
  │  (StyleData)     │
  │                  │
  │  • HeaderPipe    │
  │  • TitlePipe     │
  │  • AuthorPipe    │
  │  • YourPipe      │
  └──────────────────┘
```

### Directory Structure

```
boxuk_patterns/
├── src/
│   ├── Pipe/                         # ⚙️ Framework (black box)
│   │   ├── Contract/
│   │   │   ├── PipeContract.php
│   │   │   └── PipelineContract.php
│   │   ├── BasePipe.php
│   │   ├── BasePipeline.php
│   │   ├── Pipeline.php
│   │   └── README.md
│   │
│   ├── StyleData/                    # 👨‍💻 Application code
│   │   └── Pipe/
│   │       ├── HeaderPipe.php
│   │       ├── TitlePipe.php
│   │       ├── AuthorPipe.php
│   │       ├── DatePipe.php
│   │       └── README.md
│   │
│   ├── ArticleFormatter.php          # Facade service
│   │
│   └── Entity/
│       └── Node/
│           └── BoxUkArticle.php      # Bundle class (uses both patterns)
│
├── templates/
│   └── node--article.html.twig       # Template consuming data
│
├── boxuk_patterns.services.yml       # Service registration
└── docs/                             # Documentation
```

---

## Design Principles

### SOLID Principles

**Single Responsibility**
- Each pipe handles one specific transformation
- ArticleFormatter handles one type of formatting
- BoxUkArticle delegates, doesn't implement

**Open/Closed**
- Open for extension (add new pipes/methods)
- Closed for modification (don't change existing code)

**Dependency Inversion**
- High-level code depends on abstractions (interfaces)
- Not on concrete implementations

### Other Patterns Used

1. **Chain of Responsibility** - Each pipe handles its own data
2. **Strategy Pattern** - Pipes are interchangeable strategies
3. **Factory Pattern** - `Pipeline::create()` factory method
4. **Fluent Interface** - Method chaining for readability

---

## Summary

The BoxUK Patterns module demonstrates how **two different patterns** work together:

- **Pipeline Pattern** - For complex, extensible data processing
- **Facade Pattern** - For simple, clean API access

**Choose the right pattern for each situation, or use both together!**

Both patterns promote:
- ✅ Clean, maintainable code
- ✅ Easy testing
- ✅ Better developer experience
- ✅ Separation of concerns
- ✅ Type safety

---

## Next Steps

- **Get started**: [01-getting-started.md](01-getting-started.md) - Quick start guide
- **See examples**: [03-examples.md](03-examples.md) - Real-world pipe examples
- **API reference**: [04-api-reference.md](04-api-reference.md) - All available methods
