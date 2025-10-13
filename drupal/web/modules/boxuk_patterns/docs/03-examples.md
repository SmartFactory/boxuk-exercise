# Pipe Examples

> **Note**: All pipes should be created in `src/StyleData/Pipe/`

## Example 1: Simple Field Value Pipe

Extract a single field value:

```php
<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

class SubtitlePipe extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    if (!$object->hasField('field_subtitle')) {
      return [];
    }

    return [
      'subtitle' => $object->get('field_subtitle')->value,
    ];
  }

}
```

## Example 2: Image Field Pipe

Extract image URL and alt text:

```php
<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

class FeaturedImagePipe extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    if (!$object->hasField('field_image')) {
      return [];
    }

    $image = $object->get('field_image')->entity;

    if (!$image) {
      return [];
    }

    return [
      'featured_image' => [
        'url' => $image->createFileUrl(),
        'alt' => $object->get('field_image')->alt,
        'title' => $object->get('field_image')->title,
      ],
    ];
  }

}
```

## Example 3: Taxonomy Terms Pipe

Extract multiple taxonomy terms:

```php
<?php

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
      $term = $item->entity;

      if ($term) {
        $categories[] = [
          'id' => $term->id(),
          'name' => $term->getName(),
          'url' => $term->toUrl()->toString(),
        ];
      }
    }

    return [
      'categories' => $categories,
    ];
  }

}
```

## Example 4: Entity Reference Pipe

Extract data from a referenced entity:

```php
<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

class RelatedArticlePipe extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    if (!$object->hasField('field_related_article')) {
      return [];
    }

    $related = $object->get('field_related_article')->entity;

    if (!$related) {
      return [];
    }

    return [
      'related_article' => [
        'title' => $related->getTitle(),
        'url' => $related->toUrl()->toString(),
        'summary' => $related->get('body')->summary ?? '',
      ],
    ];
  }

}
```

## Example 5: Computed Data Pipe

Calculate values based on multiple fields:

```php
<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

class ReadingTimePipe extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    if (!$object->hasField('body')) {
      return [];
    }

    $body = $object->get('body')->value;
    $wordCount = str_word_count(strip_tags($body));
    $readingTime = ceil($wordCount / 200); // Average reading speed: 200 words/min

    return [
      'reading_time' => [
        'minutes' => $readingTime,
        'words' => $wordCount,
        'formatted' => $readingTime . ' min read',
      ],
    ];
  }

}
```

## Example 6: Conditional Logic Pipe

Return different data based on conditions:

```php
<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

class StatusBadgePipe extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    $badge = [
      'class' => '',
      'label' => '',
    ];

    if (!$object->isPublished()) {
      $badge = [
        'class' => 'badge--draft',
        'label' => 'Draft',
      ];
    }
    elseif ($object->hasField('field_featured') && $object->get('field_featured')->value) {
      $badge = [
        'class' => 'badge--featured',
        'label' => 'Featured',
      ];
    }
    elseif ($this->isRecent($object)) {
      $badge = [
        'class' => 'badge--new',
        'label' => 'New',
      ];
    }

    return ['badge' => $badge];
  }

  private function isRecent(NodeInterface $node): bool {
    $created = $node->getCreatedTime();
    $daysSinceCreated = (time() - $created) / 86400;
    return $daysSinceCreated <= 7;
  }

}
```

## Example 7: Using Drupal Services

Inject services for complex logic:

```php
<?php

namespace Drupal\boxuk_patterns\Pipe;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

class CommentCountPipe extends BasePipe {

  public function __construct(
    $object,
    protected EntityTypeManagerInterface $entityTypeManager
  ) {
    parent::__construct($object);
  }

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    $commentStorage = $this->entityTypeManager->getStorage('comment');

    $query = $commentStorage->getQuery()
      ->condition('entity_id', $object->id())
      ->condition('entity_type', 'node')
      ->condition('status', 1)
      ->accessCheck(TRUE);

    $count = $query->count()->execute();

    return [
      'comments' => [
        'count' => $count,
        'enabled' => $object->get('comment')->status != 0,
      ],
    ];
  }

}

// Usage in BoxUkArticle.php:
public function getStyleData(): array {
  $entityTypeManager = \Drupal::entityTypeManager();

  return Pipeline::create()
    ->send($this)
    ->through([
      HeaderPipe::class,
      new CommentCountPipe($this, $entityTypeManager),
    ])
    ->thenReturn();
}
```

## Example 8: Multi-value Fields Pipe

Process multiple values from a field:

```php
<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

class GalleryPipe extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    if (!$object->hasField('field_gallery')) {
      return [];
    }

    $images = [];

    foreach ($object->get('field_gallery') as $item) {
      $image = $item->entity;

      if ($image) {
        $images[] = [
          'url' => $image->createFileUrl(),
          'alt' => $item->alt,
          'width' => $item->width,
          'height' => $item->height,
        ];
      }
    }

    return [
      'gallery' => [
        'images' => $images,
        'count' => count($images),
      ],
    ];
  }

}
```

## Example 9: Link Field Pipe

Extract link field with URL and title:

```php
<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

class CallToActionPipe extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    if (!$object->hasField('field_cta_link')) {
      return [];
    }

    $link = $object->get('field_cta_link')->first();

    if (!$link) {
      return [];
    }

    return [
      'cta' => [
        'url' => $link->getUrl()->toString(),
        'title' => $link->title ?: 'Learn More',
        'external' => $link->getUrl()->isExternal(),
      ],
    ];
  }

}
```

## Example 10: Date Range Pipe

Process date range fields:

```php
<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

class EventDatePipe extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    if (!$object->hasField('field_event_date')) {
      return [];
    }

    $dateField = $object->get('field_event_date')->first();

    if (!$dateField) {
      return [];
    }

    $startDate = strtotime($dateField->value);
    $endDate = strtotime($dateField->end_value);

    return [
      'event' => [
        'start' => [
          'timestamp' => $startDate,
          'formatted' => date('F j, Y g:i A', $startDate),
          'date' => date('Y-m-d', $startDate),
          'time' => date('g:i A', $startDate),
        ],
        'end' => [
          'timestamp' => $endDate,
          'formatted' => date('F j, Y g:i A', $endDate),
          'date' => date('Y-m-d', $endDate),
          'time' => date('g:i A', $endDate),
        ],
        'is_multiday' => date('Y-m-d', $startDate) !== date('Y-m-d', $endDate),
      ],
    ];
  }

}
```

## Template Usage Examples

### Using Simple Data

```twig
{# From SubtitlePipe #}
{% if style_data.subtitle %}
  <h2 class="subtitle">{{ style_data.subtitle }}</h2>
{% endif %}
```

### Using Image Data

```twig
{# From FeaturedImagePipe #}
{% if style_data.featured_image %}
  <img src="{{ style_data.featured_image.url }}"
       alt="{{ style_data.featured_image.alt }}"
       class="featured-image">
{% endif %}
```

### Using Array of Items

```twig
{# From CategoryPipe #}
{% if style_data.categories %}
  <div class="categories">
    {% for category in style_data.categories %}
      <a href="{{ category.url }}" class="category-tag">
        {{ category.name }}
      </a>
    {% endfor %}
  </div>
{% endif %}
```

### Using Computed Data

```twig
{# From ReadingTimePipe #}
{% if style_data.reading_time %}
  <span class="reading-time">
    {{ style_data.reading_time.formatted }}
    ({{ style_data.reading_time.words }} words)
  </span>
{% endif %}
```

### Using Conditional Data

```twig
{# From StatusBadgePipe #}
{% if style_data.badge.label %}
  <span class="badge {{ style_data.badge.class }}">
    {{ style_data.badge.label }}
  </span>
{% endif %}
```

## Complete Working Example

Here's a complete example of creating a "Social Share" pipe:

**src/Pipe/SocialSharePipe.php:**
```php
<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

class SocialSharePipe extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    $url = $object->toUrl('canonical', ['absolute' => TRUE])->toString();
    $title = $object->getTitle();
    $encodedUrl = urlencode($url);
    $encodedTitle = urlencode($title);

    return [
      'social_share' => [
        'twitter' => "https://twitter.com/intent/tweet?url={$encodedUrl}&text={$encodedTitle}",
        'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}",
        'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url={$encodedUrl}",
        'email' => "mailto:?subject={$encodedTitle}&body={$encodedUrl}",
      ],
    ];
  }

}
```

**Register in BoxUkArticle.php:**
```php
use Drupal\boxuk_patterns\StyleData\Pipe\SocialSharePipe;

public function getStyleData(): array {
  return Pipeline::create()
    ->send($this)
    ->through([
      HeaderPipe::class,
      SocialSharePipe::class,
    ])
    ->thenReturn();
}
```

**Use in template:**
```twig
{% if style_data.social_share %}
  <div class="social-share">
    <a href="{{ style_data.social_share.twitter }}" target="_blank">
      Share on Twitter
    </a>
    <a href="{{ style_data.social_share.facebook }}" target="_blank">
      Share on Facebook
    </a>
    <a href="{{ style_data.social_share.linkedin }}" target="_blank">
      Share on LinkedIn
    </a>
    <a href="{{ style_data.social_share.email }}">
      Share via Email
    </a>
  </div>
{% endif %}
```

## Example 11: Using Facade Methods with Reusable Twig Components

This example demonstrates how to create a pipe that uses facade methods from the bundle class and pairs it with a reusable Twig component for consistent display.

**src/StyleData/Pipe/ReadingStatsPipe.php:**
```php
<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

/**
 * Pipe to extract reading statistics for the article.
 *
 * This pipe demonstrates using facade methods (getWordCount, getReadingTime)
 * that are defined on the BoxUkArticle entity. These methods delegate to the
 * ArticleFormatter service, keeping the logic testable and reusable.
 */
class ReadingStatsPipe extends BasePipe {

  /**
   * {@inheritdoc}
   */
  public function handle(): array {
    $object = $this->getObject();

    // Ensure we have a node entity before accessing its properties.
    if (!$object instanceof NodeInterface) {
      return [];
    }

    // Get word count and reading time from the node's facade methods.
    $wordCount = $object->getWordCount();
    $readingTime = $object->getReadingTime();

    // Return empty array if there's no content to read.
    if ($wordCount === 0) {
      return [];
    }

    return [
      'reading_stats' => [
        'word_count' => $wordCount,
        'word_count_formatted' => number_format($wordCount) . ' ' . $this->pluralize('word', $wordCount),
        'reading_time' => $readingTime,
        'reading_time_formatted' => $readingTime . ' min read',
        'reading_speed' => 200, // Words per minute (for reference).
      ],
    ];
  }

  /**
   * Helper method to pluralize words.
   */
  private function pluralize(string $word, int $count): string {
    return $count === 1 ? $word : $word . 's';
  }

}
```

**templates/reading-stats.html.twig:**
```twig
{#
/**
 * @file
 * Reusable component for displaying article reading statistics.
 *
 * This component displays word count and estimated reading time for articles.
 * It's designed to be reusable across different content types and layouts.
 *
 * Available variables:
 * - word_count: (int) The number of words in the article (required)
 * - reading_time: (int) The estimated reading time in minutes (required)
 * - format: (string) Display format: 'inline' or 'block' (default: 'inline')
 * - show_word_count: (bool) Whether to show word count (default: true)
 * - show_reading_time: (bool) Whether to show reading time (default: true)
 * - separator: (string) Separator between stats (default: '•')
 * - classes: (array) Additional CSS classes to apply
 *
 * Usage examples:
 *
 * Basic usage (inline format):
 * @code
 * {% include '@boxuk_patterns/reading-stats.html.twig' with {
 *   'word_count': 1234,
 *   'reading_time': 6
 * } %}
 * @endcode
 *
 * Block format with only reading time:
 * @code
 * {% include '@boxuk_patterns/reading-stats.html.twig' with {
 *   'word_count': 1234,
 *   'reading_time': 6,
 *   'format': 'block',
 *   'show_word_count': false
 * } %}
 * @endcode
 */
#}

{# Set default values #}
{% set format = format|default('inline') %}
{% set show_word_count = show_word_count is defined ? show_word_count : true %}
{% set show_reading_time = show_reading_time is defined ? show_reading_time : true %}
{% set separator = separator|default('•') %}
{% set additional_classes = classes|default([]) %}

{# Build CSS classes #}
{% set css_classes = [
  'reading-stats',
  'reading-stats--' ~ format,
] %}

{# Add any additional classes passed in #}
{% if additional_classes %}
  {% set css_classes = css_classes|merge(additional_classes) %}
{% endif %}

{# Only render if we have at least one stat to show #}
{% if (show_word_count or show_reading_time) and (word_count or reading_time) %}
  <div class="{{ css_classes|join(' ') }}" role="complementary" aria-label="Reading statistics">

    {# Word count #}
    {% if show_word_count and word_count %}
      <span class="reading-stats__item reading-stats__word-count">
        <span class="reading-stats__value">{{ word_count|number_format }}</span>
        <span class="reading-stats__label">{{ word_count == 1 ? 'word' : 'words' }}</span>
      </span>
    {% endif %}

    {# Separator (only if showing both stats) #}
    {% if show_word_count and show_reading_time and word_count and reading_time %}
      <span class="reading-stats__separator" aria-hidden="true">{{ separator }}</span>
    {% endif %}

    {# Reading time #}
    {% if show_reading_time and reading_time %}
      <span class="reading-stats__item reading-stats__reading-time">
        <span class="reading-stats__value">{{ reading_time }}</span>
        <span class="reading-stats__label">min read</span>
      </span>
    {% endif %}

  </div>
{% endif %}
```

**Register in BoxUkArticle.php:**
```php
use Drupal\boxuk_patterns\StyleData\Pipe\ReadingStatsPipe;

public function getStyleData(): array {
  return Pipeline::create()
    ->send($this)
    ->through([
      HeaderPipe::class,
      TitlePipe::class,
      AuthorPipe::class,
      DatePipe::class,
      ReadingStatsPipe::class,
    ])
    ->thenReturn();
}
```

**Use in node--article.html.twig template:**
```twig
{% set style_data = node.getStyleData() %}

{# Basic usage with default settings #}
{% if style_data.reading_stats %}
  {% include '@boxuk_patterns/reading-stats.html.twig' with {
    'word_count': style_data.reading_stats.word_count,
    'reading_time': style_data.reading_stats.reading_time
  } %}
{% endif %}

{# Custom format with only reading time #}
{% if style_data.reading_stats %}
  {% include '@boxuk_patterns/reading-stats.html.twig' with {
    'word_count': style_data.reading_stats.word_count,
    'reading_time': style_data.reading_stats.reading_time,
    'format': 'block',
    'show_word_count': false,
    'classes': ['my-custom-class']
  } %}
{% endif %}
```

### Benefits of This Approach:

1. **Separation of Concerns**: Pipe extracts data, component handles display
2. **Reusability**: Component can be used anywhere with any data source
3. **Flexibility**: Component supports multiple display formats and options
4. **Testability**: Pipe can be unit tested, component can be visually tested
5. **Maintainability**: Changes to display logic only require updating the component
6. **Accessibility**: Component includes proper ARIA labels and semantic HTML
7. **Facade Pattern**: Business logic is encapsulated in service methods

## Tips

1. **Return early** - Check conditions and return empty array when data isn't available
2. **Type check** - Always verify the object is the expected type
3. **Field existence** - Check if fields exist before accessing them
4. **Null safety** - Check if entities/values exist before using them
5. **Meaningful keys** - Use descriptive array keys that make sense in templates
6. **Structured data** - Return well-organized nested arrays for complex data
7. **Performance** - Keep pipe logic lightweight; cache if needed
8. **Reusability** - Make pipes generic enough to reuse across different contexts
9. **Use facade methods** - Delegate to service layer for complex logic
10. **Create reusable components** - Extract common UI patterns into Twig includes
