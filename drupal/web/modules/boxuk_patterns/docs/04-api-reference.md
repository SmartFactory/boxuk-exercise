# API Reference

## Table of Contents

- [Pipeline API](#pipeline-api)
- [Facade API](#facade-api)
- [Base Classes](#base-classes)

---

## Pipeline API

### Pipeline Class

**Namespace**: `Drupal\boxuk_patterns\Pipe\Pipeline`

#### Methods

| Method | Description | Returns |
|--------|-------------|---------|
| `create()` | Create a new pipeline instance | `Pipeline` |
| `send(object $object)` | Set the object to process | `$this` |
| `through(array $pipes)` | Set pipes to process through | `$this` |
| `thenMerge(array $data)` | Merge additional data | `$this` |
| `thenReturn()` | Execute and return merged array | `array` |

#### Example

```php
Pipeline::create()
  ->send($node)
  ->through([HeaderPipe::class, TitlePipe::class])
  ->thenMerge(['extra' => 'data'])
  ->thenReturn();
```

### BasePipe Class

**Namespace**: `Drupal\boxuk_patterns\Pipe\BasePipe`

#### Methods

| Method | Description | Returns |
|--------|-------------|---------|
| `__construct(object $object)` | Constructor | `void` |
| `getObject()` | Get the object being processed | `object` |
| `handle()` | Process and return data (abstract) | `array` |

#### Example

```php
class MyPipe extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    return ['key' => 'value'];
  }
}
```

---

## Facade API

### BoxUkArticle Bundle Class

**Namespace**: `Drupal\boxuk_patterns\Entity\Node\BoxUkArticle`

### Getter Methods (Read-Only)

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `getSubtitle()` | - | `?string` | Get article subtitle |
| `getSummary()` | `?int $length = NULL` | `?string` | Get summary (trimmed to length) |
| `getReadingTime()` | `int $wordsPerMinute = 200` | `int` | Get reading time in minutes |
| `getWordCount()` | - | `int` | Get total word count |
| `isFeatured()` | - | `bool` | Check if featured |
| `getFeaturedImageUrl()` | `string $imageStyle = ''` | `?string` | Get image URL (optionally styled) |
| `getFeaturedImageAlt()` | - | `?string` | Get image alt text |
| `getPublishedDate()` | `string $format = 'F j, Y'` | `string` | Get formatted publish date |
| `getUpdatedDate()` | `string $format = 'F j, Y'` | `string` | Get formatted update date |
| `getAuthorName()` | - | `string` | Get author's display name |
| `isRecent()` | `int $days = 7` | `bool` | Check if published within X days |
| `getTags()` | - | `array` | Get all tag names |
| `getCategories()` | - | `array` | Get all category names |

### Setter Methods (Write)

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `setSubtitle()` | `string $subtitle` | `$this` | Set subtitle (fluent) |
| `setFeatured()` | `bool $featured` | `$this` | Set featured status (fluent) |

### Pipeline Method

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `getStyleData()` | - | `array` | Get processed pipeline data |

---

## Usage Examples

### Facade Methods

#### Basic Getters

```php
/** @var \Drupal\boxuk_patterns\Entity\Node\BoxUkArticle $article */

// Simple values
$subtitle = $article->getSubtitle();        // string|null
$readingTime = $article->getReadingTime();  // int
$wordCount = $article->getWordCount();      // int

// Boolean checks
$isFeatured = $article->isFeatured();       // bool
$isRecent = $article->isRecent(7);          // bool (7 days)

// Arrays
$tags = $article->getTags();                // array of strings
$categories = $article->getCategories();    // array of strings
```

#### Getters with Parameters

```php
// Custom summary length
$shortSummary = $article->getSummary(100);
$longSummary = $article->getSummary(300);

// Custom reading speed
$normalSpeed = $article->getReadingTime(200);  // 200 words/min
$slowSpeed = $article->getReadingTime(150);    // 150 words/min

// Custom date format
$usDate = $article->getPublishedDate('m/d/Y');     // 12/31/2024
$isoDate = $article->getPublishedDate('Y-m-d');    // 2024-12-31
$longDate = $article->getPublishedDate('F j, Y');  // December 31, 2024

// Image with style
$largeImage = $article->getFeaturedImageUrl('large');
$thumbnail = $article->getFeaturedImageUrl('thumbnail');
```

#### Setters (Fluent Interface)

```php
// Single setter
$article->setSubtitle('New subtitle');
$article->save();

// Fluent chaining
$article
  ->setSubtitle('New subtitle')
  ->setFeatured(TRUE)
  ->save();
```

#### In Templates

```twig
{# Simple values #}
<h2>{{ node.getSubtitle() }}</h2>
<p>{{ node.getSummary(150) }}</p>
<span>{{ node.getReadingTime() }} min read</span>

{# Boolean checks #}
{% if node.isFeatured() %}
  <span class="badge">Featured</span>
{% endif %}

{% if node.isRecent(7) %}
  <span class="badge badge-new">New</span>
{% endif %}

{# Arrays #}
{% if node.getTags() %}
  <div class="tags">
    {% for tag in node.getTags() %}
      <span class="tag">{{ tag }}</span>
    {% endfor %}
  </div>
{% endif %}

{# Custom parameters #}
<time datetime="{{ node.getPublishedDate('Y-m-d') }}">
  {{ node.getPublishedDate('F j, Y') }}
</time>

{# Images #}
{% if node.getFeaturedImageUrl() %}
  <img src="{{ node.getFeaturedImageUrl('large') }}"
       alt="{{ node.getFeaturedImageAlt() }}">
{% endif %}
```

### Pipeline Method

```php
// Get all pipeline data
$styleData = $article->getStyleData();

// Access nested data
$header = $styleData['header'] ?? '';
$titleText = $styleData['title']['text'] ?? '';
$authorName = $styleData['author']['name'] ?? '';
```

```twig
{# In templates #}
{% set style_data = node.getStyleData() %}

{{ style_data.header }}
{{ style_data.title.formatted }}
{{ style_data.author.name }}
{{ style_data.dates.created.formatted }}
```

---

## Base Classes

### BasePipe

**Abstract class for creating pipes**

```php
<?php

namespace Drupal\boxuk_patterns\Pipe;

abstract class BasePipe implements Contract\PipeContract {

  public function __construct(protected $object) {}

  public function getObject(): object {
    return $this->object;
  }

  abstract public function handle(): array;
}
```

**Usage**:

```php
use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

class MyPipe extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    // Your logic here
    return ['key' => 'value'];
  }
}
```

### BasePipeline

**Abstract class for pipeline implementations**

**Methods**:
- `send(object $object): self` - Set object to process
- `through(array $pipes): self` - Set pipes
- `thenMerge(array $data): self` - Merge extra data
- `thenReturn(): array` - Execute and return result

**Internal Methods**:
- `pipeIsValid(string $pipe): bool` - Check if pipe is valid
- `arrayMergeDeep(array $a1, array $a2): array` - Deep merge arrays

---

## Service Registration

### boxuk_patterns.services.yml

```yaml
services:
  boxuk_patterns.pipeline:
    class: Drupal\boxuk_patterns\Pipe\Pipeline
    factory: [Drupal\boxuk_patterns\Pipe\Pipeline, create]

  boxuk_patterns.article_formatter:
    class: Drupal\boxuk_patterns\ArticleFormatter
```

### Using Services

```php
// Get pipeline service
$pipeline = \Drupal::service('boxuk_patterns.pipeline');

// Get formatter service
$formatter = \Drupal::service('boxuk_patterns.article_formatter');
$subtitle = $formatter->getSubtitle($node);
```

### With Dependency Injection

```php
class MyService {

  public function __construct(
    protected ArticleFormatter $formatter,
    protected Pipeline $pipeline
  ) {}

  public function process($article) {
    $subtitle = $this->formatter->getSubtitle($article);
    $styleData = $this->pipeline
      ->send($article)
      ->through([...])
      ->thenReturn();
  }
}
```

---

## Type Signatures

### Common Types

```php
// Pipe handle() return type
public function handle(): array

// Facade getters
public function getSubtitle(): ?string
public function getReadingTime(int $wordsPerMinute = 200): int
public function isFeatured(): bool
public function getTags(): array

// Facade setters (fluent)
public function setSubtitle(string $subtitle): self
public function setFeatured(bool $featured): self

// Pipeline methods
public static function create(): self
public function send(object $object): self
public function through(array $pipes): self
public function thenReturn(): array
```

---

## Quick Reference

### I want to...

**...get simple data from an article**
→ Use facade methods: `$article->getSubtitle()`

**...get complex structured data**
→ Use pipeline: `$article->getStyleData()`

**...create custom data for templates**
→ Create a new pipe in `src/StyleData/Pipe/`

**...add a new facade method**
→ Add to `ArticleFormatter.php`, then add facade method to `BoxUkArticle.php`

**...check if an article is featured**
→ `$article->isFeatured()`

**...get reading time**
→ `$article->getReadingTime()`

**...set a field value**
→ `$article->setSubtitle('text')->save()`

**...use in a template**
→ `{{ node.getSubtitle() }}` or `{{ style_data.key }}`

---

## See Also

- **Getting Started**: [01-getting-started.md](01-getting-started.md)
- **Pattern Details**: [02-patterns.md](02-patterns.md)
- **Real Examples**: [03-examples.md](03-examples.md)
