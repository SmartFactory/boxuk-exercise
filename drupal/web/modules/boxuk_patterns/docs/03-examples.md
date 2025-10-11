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

## Tips

1. **Return early** - Check conditions and return empty array when data isn't available
2. **Type check** - Always verify the object is the expected type
3. **Field existence** - Check if fields exist before accessing them
4. **Null safety** - Check if entities/values exist before using them
5. **Meaningful keys** - Use descriptive array keys that make sense in templates
6. **Structured data** - Return well-organized nested arrays for complex data
7. **Performance** - Keep pipe logic lightweight; cache if needed
8. **Reusability** - Make pipes generic enough to reuse across different contexts
