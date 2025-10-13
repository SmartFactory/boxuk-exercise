# Style Data Pipes

## 👨‍💻 This is where you work!

This directory contains **your custom pipes** - the pipes you create to add data to your templates.

## Quick Start

### 1. Create a New Pipe

Create a new file in this directory (e.g., `MyCustomPipe.php`):

```php
<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

class MyCustomPipe extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    // Your logic here
    return [
      'my_key' => 'my_value',
    ];
  }

}
```

### 2. Register Your Pipe

Add it to `src/Entity/Node/BoxUkArticle.php`:

```php
use Drupal\boxuk_patterns\StyleData\Pipe\MyCustomPipe;

public function getStyleData(): array {
  return Pipeline::create()
    ->send($this)
    ->through([
      HeaderPipe::class,
      MyCustomPipe::class,  // <-- Add here
    ])
    ->thenReturn();
}
```

### 3. Use in Template

Use it in `templates/node--article.html.twig`:

```twig
{% set style_data = node.getStyleData() %}

{{ style_data.my_key }}
```

## Example Pipes in This Directory

- **HeaderPipe.php** - Simple static data example
- **TitlePipe.php** - Accessing node title
- **AuthorPipe.php** - Accessing related entities (author)
- **DatePipe.php** - Formatting timestamps

## What You Need to Know

### Required Method

Every pipe MUST implement the `handle()` method:

```php
public function handle(): array {
  // Return an associative array
  return ['key' => 'value'];
}
```

### Access the Node

Get the node entity that was passed to the pipeline:

```php
$object = $this->getObject();
```

### Type Safety

Always check the object type before using it:

```php
if (!$object instanceof NodeInterface) {
  return [];
}
```

### Return Empty Arrays

When you have no data, return an empty array (not `null`):

```php
if (!$object->hasField('field_custom')) {
  return [];  // ✅ Good
}

// return null;  ❌ Bad
```

## Common Patterns

### Access a Field Value

```php
if ($object->hasField('field_subtitle')) {
  return ['subtitle' => $object->get('field_subtitle')->value];
}
```

### Access Multiple Values (Tags, Categories, etc.)

```php
$tags = [];
foreach ($object->get('field_tags') as $item) {
  $tags[] = $item->entity->getName();
}
return ['tags' => $tags];
```

### Access Image Field

```php
$image = $object->get('field_image')->entity;
if ($image) {
  return [
    'image' => [
      'url' => $image->createFileUrl(),
      'alt' => $object->get('field_image')->alt,
    ],
  ];
}
```

## Don't Need to Understand the Framework

**You don't need to understand how the pipeline works!**

The pipeline framework lives in `src/Pipe/` - that's the "black box" you don't need to look at. Just:

1. Extend `BasePipe`
2. Implement `handle()`
3. Return an array

That's it!

## Documentation

For more examples and detailed documentation, see:

- **`/QUICK_START.md`** - Quick reference guide
- **`/EXAMPLES.md`** - 10+ real-world examples
- **`/README.md`** - Complete documentation
- **`/ARCHITECTURE.md`** - Architecture details (if you're curious)

## Testing

After creating a new pipe:

```bash
ddev drush cr
```

Then view an article node to see your data.

## Need Help?

Check the example pipes in this directory - they show the most common patterns you'll need!
