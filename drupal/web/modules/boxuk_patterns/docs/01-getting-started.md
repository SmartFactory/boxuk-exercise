# Getting Started with BoxUK Patterns

## Quick Start: Create Your First Pipe

### Step 1: Create the Pipe Class

Create a new file in `src/StyleData/Pipe/YourPipeName.php`:

```php
<?php

namespace Drupal\boxuk_patterns\StyleData\Pipe;

use Drupal\boxuk_patterns\Pipe\BasePipe;
use Drupal\node\NodeInterface;

class YourPipeName extends BasePipe {

  public function handle(): array {
    $object = $this->getObject();

    if (!$object instanceof NodeInterface) {
      return [];
    }

    // Add your logic here
    return [
      'your_key' => 'your_value',
    ];
  }

}
```

### Step 2: Register the Pipe

Edit `src/Entity/Node/BoxUkArticle.php`:

```php
use Drupal\boxuk_patterns\StyleData\Pipe\YourPipeName;

public function getStyleData(): array {
  return Pipeline::create()
    ->send($this)
    ->through([
      HeaderPipe::class,
      TitlePipe::class,
      YourPipeName::class,  // <-- Add here
    ])
    ->thenReturn();
}
```

### Step 3: Use in Template

Edit `templates/node--article.html.twig`:

```twig
{% set style_data = node.getStyleData() %}

{% if style_data.your_key %}
  <div>{{ style_data.your_key }}</div>
{% endif %}
```

### Step 4: Clear Cache

```bash
ddev drush cr
```

Done! Your pipe is now processing data for templates.

---

## Understanding the Directory Structure

### Where You Work

```
src/
├── Pipe/                    # ⚙️ FRAMEWORK (don't modify)
│   ├── Contract/
│   ├── BasePipe.php
│   ├── BasePipeline.php
│   └── Pipeline.php
│
├── StyleData/
│   └── Pipe/                # 👨‍💻 YOUR WORKSPACE (work here!)
│       ├── HeaderPipe.php   # Example pipes
│       ├── TitlePipe.php
│       ├── AuthorPipe.php
│       └── DatePipe.php
│
└── Entity/
    └── Node/
        └── BoxUkArticle.php # Register pipes here
```

### The Simple Rule

**✅ Create pipes in**: `src/StyleData/Pipe/`
**❌ Don't modify**: `src/Pipe/`

### Why This Structure?

**Problem We Solved:**
- Developers saw framework code they didn't need to understand
- Wasn't clear what was "infrastructure" vs "application code"
- Risk of accidentally modifying framework files

**Solution:**
- Framework code in `src/Pipe/` (black box)
- Your code in `src/StyleData/Pipe/` (your workspace)
- Clear separation = less confusion

### Why "StyleData"?

- Describes what pipes do (provide data for styling/rendering)
- Not "Plugin" (which has specific meaning in Drupal)
- Clearly separates application code from framework

---

## Common Patterns

### Accessing a Field

```php
if ($object->hasField('field_my_field')) {
  $value = $object->get('field_my_field')->value;
  return ['my_data' => $value];
}
```

### Accessing Referenced Entities

```php
if ($object->hasField('field_reference')) {
  $referenced = $object->get('field_reference')->entity;
  if ($referenced) {
    return ['referenced_title' => $referenced->label()];
  }
}
```

### Multiple Values (Tags, Categories)

```php
if ($object->hasField('field_tags')) {
  $tags = [];
  foreach ($object->get('field_tags') as $item) {
    $tags[] = $item->entity->label();
  }
  return ['tags' => $tags];
}
```

### Conditional Logic

```php
if ($object->isPublished()) {
  return ['status' => 'Published'];
} else {
  return ['status' => 'Draft'];
}
```

---

## Using the Facade Pattern

The module also provides clean getter/setter methods:

```php
// ✅ Clean facade methods
$subtitle = $article->getSubtitle();
$readingTime = $article->getReadingTime();
$tags = $article->getTags();

// Set values (fluent interface)
$article
  ->setSubtitle('New subtitle')
  ->setFeatured(TRUE)
  ->save();

// In templates
{{ node.getSubtitle() }}
{{ node.getReadingTime() }} min read
```

See [04-api-reference.md](04-api-reference.md) for all available methods.

---

## Troubleshooting

### Pipeline returns empty array?
- Check that your pipe extends `BasePipe`
- Make sure you're returning an array, not null
- Verify the pipe is registered in `getStyleData()`

### Data not showing in template?
- Clear Drupal cache: `ddev drush cr`
- Check the array key matches between pipe and template
- Use `{{ dump(style_data) }}` to see all available data

### Class not found error?
- Make sure the namespace matches the file path
- Clear Drupal cache
- Check for typos in class name

### Methods not working?
- Ensure service is registered in `boxuk_patterns.services.yml`
- Clear cache after adding new methods
- Check method exists in `ArticleFormatter` service

---

## Developer Experience Benefits

### 1. Clear Mental Model

```
Framework (black box)     →     Application (your code)
     src/Pipe/            →     src/StyleData/Pipe/
   "Don't touch"          →     "Work here"
```

### 2. Less Cognitive Load

You don't need to:
- Understand pipeline internals
- Know about contracts/interfaces
- Worry about breaking framework code
- Filter through framework files to find examples

### 3. Self-Documenting

- `src/Pipe/README.md` - "Framework code, don't modify"
- `src/StyleData/Pipe/README.md` - "Create your pipes here"

### 4. Example Pipes Right Where You Work

Example pipes are in `src/StyleData/Pipe/` alongside where you'll create your own pipes, making them easy to reference and copy.

---

## Best Practices

### DO ✅

- Create all new pipes in `src/StyleData/Pipe/`
- Use the namespace `Drupal\boxuk_patterns\StyleData\Pipe`
- Extend `BasePipe` from the framework
- Look at example pipes for patterns
- Return empty arrays when no data (not null)
- Check if fields exist before accessing them

### DON'T ❌

- Modify files in `src/Pipe/` (framework code)
- Create pipes in `src/Pipe/` (wrong location)
- Put complex logic in bundle class (use service instead)
- Forget to clear cache after changes

---

## Quick Reference

| Question | Answer |
|----------|--------|
| Where do I create a new pipe? | `src/StyleData/Pipe/` |
| What namespace should I use? | `Drupal\boxuk_patterns\StyleData\Pipe` |
| Can I modify BasePipe.php? | No, that's framework code. Just extend it. |
| Where are the examples? | `src/StyleData/Pipe/` - right where you work! |
| Do I need to understand how Pipeline works? | No! Just extend BasePipe and implement handle(). |
| How do I use facade methods? | Call them directly on the entity: `$article->getSubtitle()` |

---

## Next Steps

- **See examples**: [03-examples.md](03-examples.md) - 10+ real-world pipe examples
- **Understand patterns**: [02-patterns.md](02-patterns.md) - Learn how Pipeline and Facade patterns work
- **API reference**: [04-api-reference.md](04-api-reference.md) - All available methods and classes

---

## Summary

**Key Insight**: Developers shouldn't need to understand framework internals to use a pattern effectively.

By separating framework and application code:
- ✅ Lower barrier to entry
- ✅ Less cognitive load
- ✅ Clearer mental model
- ✅ Faster onboarding
- ✅ Less risk of breaking things

**You work in `src/StyleData/Pipe/`. Everything else is just infrastructure.**
