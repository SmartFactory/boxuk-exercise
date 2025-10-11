# Testing Guide

This guide covers how to run and write tests for the boxuk_patterns module.

> **Note**: This project uses a **centralized `web/phpunit.xml`** configuration that works for all custom modules. You don't need per-module PHPUnit configuration files!

## Table of Contents

- [Test Overview](#test-overview)
- [Running Tests](#running-tests)
- [Test Structure](#test-structure)
- [Writing Tests](#writing-tests)
- [Continuous Integration](#continuous-integration)

---

## Test Overview

The boxuk_patterns module has comprehensive test coverage with **66 tests** and **413 assertions**:

### Test Types

**Unit Tests** (26 tests):
- No Drupal bootstrap required
- Fast execution (< 1 second)
- Test individual classes in isolation
- Use mocked dependencies

**Kernel Tests** (40 tests):
- Require Drupal bootstrap
- Slower execution (~16 seconds)
- Test integration with Drupal
- Use real database and entity system

### Test Coverage

| Component | Type | Tests | What's Tested |
|-----------|------|-------|---------------|
| BasePipeline | Unit | 10 | Array merging, pipe validation, fluent interface |
| Pipeline | Unit | 2 | Factory method |
| BasePipe | Unit | 3 | Object storage/retrieval |
| HeaderPipe | Unit | 2 | Data transformation |
| TitlePipe | Unit | 3 | Title formatting |
| AuthorPipe | Unit | 3 | Author data extraction |
| DatePipe | Unit | 3 | Date formatting |
| ArticleFormatter | Kernel | 20 | All service methods |
| BoxUkArticle | Kernel | 20 | Facade methods, pipeline integration |

---

## Running Tests

### Prerequisites

You must have the DDEV environment running and composer dependencies installed:

```bash
ddev start
ddev composer require --dev "drupal/core-dev:^10"
```

### PHPUnit Configuration

The project uses a centralized `web/phpunit.xml` file based on Drupal core's `phpunit.xml.dist`. This configuration:
- ✅ Works for **all custom modules** in your project
- ✅ Pre-configured for **DDEV environment**
- ✅ Includes both **core test suites** and **custom-only test suites**
- ✅ **No per-module configuration needed**

### Quick Start: Using the Test Script

A convenient `./test` bash script is available in the project root for easy test execution:

```bash
# Run all custom module tests
./test

# Run only unit tests (fast)
./test unit

# Run only kernel tests
./test kernel

# Run tests for specific module
./test module boxuk_patterns

# Run specific test file
./test file modules/boxuk_patterns/tests/src/Unit/Pipe/PipelineTest.php

# Run specific test method
./test filter testHandleReturnsTitleData

# Generate coverage report
./test coverage

# Show help
./test help
```

The script automatically:
- ✅ Checks if DDEV is running (starts it if needed)
- ✅ Runs tests inside the DDEV container
- ✅ Uses proper PHPUnit configuration
- ✅ Provides colored output for better readability

### Run All Custom Module Tests

Run all tests from all your custom modules (excludes Drupal core):

```bash
ddev exec "cd web && phpunit --testsuite custom-unit --testsuite custom-kernel"
```

### Run Only Unit Tests (Custom Modules)

Unit tests are fast and don't require database setup:

```bash
ddev exec "cd web && phpunit --testsuite custom-unit"
```

Expected output:
```
PHPUnit 9.6.29 by Sebastian Bergmann and contributors.

..........................                                        26 / 26 (100%)

Time: 00:00.022, Memory: 6.00 MB

OK (26 tests, 65 assertions)
```

### Run Only Kernel Tests (Custom Modules)

Kernel tests require database and take longer:

```bash
ddev exec "cd web && phpunit --testsuite custom-kernel"
```

Expected output:
```
PHPUnit 9.6.29 by Sebastian Bergmann and contributors.

........................................                          40 / 40 (100%)

Time: 00:16.482, Memory: 4.00 MB

OK (40 tests, 348 assertions)
```

### Run Specific Module Tests

Test only the boxuk_patterns module:

```bash
# All tests for this module
ddev exec "cd web && phpunit modules/boxuk_patterns"

# Only unit tests
ddev exec "cd web && phpunit modules/boxuk_patterns/tests/src/Unit"

# Only kernel tests
ddev exec "cd web && phpunit modules/boxuk_patterns/tests/src/Kernel"
```

### Run Specific Test File

```bash
# Run only BasePipelineTest
ddev exec "cd web && phpunit modules/boxuk_patterns/tests/src/Unit/Pipe/BasePipelineTest.php"

# Run only ArticleFormatterTest
ddev exec "cd web && phpunit modules/boxuk_patterns/tests/src/Kernel/ArticleFormatterTest.php"
```

### Run Specific Test Method

```bash
# Run only testHandleReturnsTitleData from TitlePipeTest
ddev exec "cd web && phpunit --filter testHandleReturnsTitleData \
  modules/boxuk_patterns/tests/src/Unit/StyleData/Pipe/TitlePipeTest.php"
```

### Available Test Suites

The `web/phpunit.xml` defines these test suites:

**Core + All Extensions** (includes Drupal core tests):
- `--testsuite unit` - All unit tests (core + custom)
- `--testsuite kernel` - All kernel tests (core + custom)
- `--testsuite functional` - All functional tests (core + custom)

**Custom Modules Only** (excludes Drupal core):
- `--testsuite custom-unit` - Unit tests from custom modules only
- `--testsuite custom-kernel` - Kernel tests from custom modules only
- `--testsuite custom-functional` - Functional tests from custom modules only

---

## Test Structure

### Directory Layout

```
drupal/
└── web/
    ├── phpunit.xml                 # ✅ Centralized config (works for all modules!)
    └── modules/
        └── boxuk_patterns/
            └── tests/
                └── src/
                    ├── Unit/                   # Unit tests (no Drupal)
                    │   ├── Pipe/
                    │   │   ├── BasePipelineTest.php
                    │   │   ├── PipelineTest.php
                    │   │   └── BasePipeTest.php
                    │   └── StyleData/
                    │       └── Pipe/
                    │           ├── HeaderPipeTest.php
                    │           ├── TitlePipeTest.php
                    │           ├── AuthorPipeTest.php
                    │           └── DatePipeTest.php
                    └── Kernel/                 # Kernel tests (with Drupal)
                        ├── ArticleFormatterTest.php
                        └── BoxUkArticleTest.php
```

### Naming Conventions

- Test files must end with `Test.php`
- Test classes must end with `Test`
- Test methods must start with `test`
- Test classes must be in the `Drupal\Tests\boxuk_patterns\` namespace

---

## Writing Tests

### When to Use Unit Tests

Use unit tests when:
- Testing **pure logic** with no Drupal dependencies
- Testing **individual methods** in isolation
- You can **mock** all dependencies
- You want **fast execution**

Example: Testing the BasePipeline array merge logic

```php
<?php

namespace Drupal\Tests\boxuk_patterns\Unit\Pipe;

use Drupal\boxuk_patterns\Pipe\Pipeline;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the BasePipeline class.
 *
 * @group boxuk_patterns
 * @coversDefaultClass \Drupal\boxuk_patterns\Pipe\BasePipeline
 */
class BasePipelineTest extends UnitTestCase {

  /**
   * Test deep array merging.
   *
   * @covers ::arrayMergeDeep
   */
  public function testDeepArrayMerge(): void {
    $pipeline = Pipeline::create();
    $object = new \stdClass();

    $result = $pipeline
      ->send($object)
      ->through([NestedPipe1::class, NestedPipe2::class])
      ->thenReturn();

    $this->assertEquals([
      'level1' => [
        'level2' => [
          'key1' => 'value1',
          'key2' => 'value2',
        ],
      ],
    ], $result);
  }
}
```

### When to Use Kernel Tests

Use kernel tests when:
- Testing **integration** with Drupal
- Testing **services** that use Drupal APIs
- Testing **entity operations**
- Testing **database interactions**

Example: Testing the ArticleFormatter service

```php
<?php

namespace Drupal\Tests\boxuk_patterns\Kernel;

use Drupal\boxuk_patterns\ArticleFormatter;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\user\Entity\User;

/**
 * Tests for the ArticleFormatter service.
 *
 * @group boxuk_patterns
 * @coversDefaultClass \Drupal\boxuk_patterns\ArticleFormatter
 */
class ArticleFormatterTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'node',
    'field',
    'text',
    'filter',
    'boxuk_patterns',
  ];

  /**
   * The article formatter service.
   *
   * @var \Drupal\boxuk_patterns\ArticleFormatter
   */
  protected ArticleFormatter $formatter;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Install entity schemas
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['filter', 'node', 'field']);

    // Create content type
    $nodeType = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $nodeType->save();

    // Get the service
    $this->formatter = $this->container->get('boxuk_patterns.article_formatter');
  }

  /**
   * Test getAuthorName returns correct name.
   *
   * @covers ::getAuthorName
   */
  public function testGetAuthorNameReturnsName(): void {
    // Create test user
    $user = User::create([
      'name' => 'Test Author',
      'mail' => 'test@example.com',
    ]);
    $user->save();

    // Create test node
    $node = Node::create([
      'type' => 'article',
      'title' => 'Test Article',
      'uid' => $user->id(),
    ]);
    $node->save();

    $result = $this->formatter->getAuthorName($node);
    $this->assertEquals('Test Author', $result);
  }
}
```

### Creating a New Pipe Test

1. Create the test file in `tests/src/Unit/StyleData/Pipe/`:

```php
<?php

namespace Drupal\Tests\boxuk_patterns\Unit\StyleData\Pipe;

use Drupal\boxuk_patterns\StyleData\Pipe\MyNewPipe;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for the MyNewPipe class.
 *
 * @group boxuk_patterns
 * @coversDefaultClass \Drupal\boxuk_patterns\StyleData\Pipe\MyNewPipe
 */
class MyNewPipeTest extends UnitTestCase {

  /**
   * Test handle returns data for a valid node.
   *
   * @covers ::handle
   */
  public function testHandleReturnsData(): void {
    // Create mock node
    $node = $this->createMock(NodeInterface::class);
    $node->expects($this->once())
      ->method('getTitle')
      ->willReturn('Test Title');

    // Create pipe and test
    $pipe = new MyNewPipe($node);
    $result = $pipe->handle();

    // Assert expectations
    $this->assertIsArray($result);
    $this->assertArrayHasKey('my_key', $result);
    $this->assertEquals('expected_value', $result['my_key']);
  }

  /**
   * Test handle returns empty array for non-node object.
   *
   * @covers ::handle
   */
  public function testHandleReturnsEmptyForNonNode(): void {
    $object = new \stdClass();
    $pipe = new MyNewPipe($object);

    $result = $pipe->handle();

    $this->assertIsArray($result);
    $this->assertEmpty($result);
  }
}
```

2. Run the new test:

```bash
ddev exec "phpunit --bootstrap /var/www/html/web/core/tests/bootstrap.php \
  /var/www/html/web/modules/boxuk_patterns/tests/src/Unit/StyleData/Pipe/MyNewPipeTest.php"
```

### Best Practices

**General**:
- One assertion per test method (when possible)
- Use descriptive test method names
- Test both happy path and edge cases
- Test error conditions

**Unit Tests**:
- Mock all dependencies
- Don't test framework code
- Keep tests fast
- Test behavior, not implementation

**Kernel Tests**:
- Install only necessary modules
- Clean up test data in tearDown (if needed)
- Use real entities when testing integrations
- Test service methods thoroughly

---

## Continuous Integration

### GitHub Actions Example

Create `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  phpunit:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, pdo_mysql

      - name: Install Dependencies
        run: composer install

      - name: Run Unit Tests
        run: vendor/bin/phpunit tests/src/Unit

      - name: Run Kernel Tests
        run: vendor/bin/phpunit tests/src/Kernel
        env:
          SIMPLETEST_BASE_URL: 'http://localhost'
          SIMPLETEST_DB: 'mysql://root:root@127.0.0.1/drupal'
```

### Test Coverage

To generate code coverage reports:

```bash
ddev exec "phpunit --bootstrap /var/www/html/web/core/tests/bootstrap.php \
  --coverage-html /var/www/html/web/modules/boxuk_patterns/coverage \
  /var/www/html/web/modules/boxuk_patterns/tests/"
```

View the coverage report at `coverage/index.html`.

---

## Troubleshooting

### Common Issues

**Issue**: `Class "\Drupal\Tests\DocumentElement" not found`
**Solution**: Install drupal/core-dev:
```bash
ddev composer require --dev "drupal/core-dev:^10"
```

**Issue**: `Test directory not found`
**Solution**: Make sure you're running from the correct directory or use absolute paths

**Issue**: Kernel tests fail with database errors
**Solution**: Make sure SIMPLETEST_DB environment variable is set correctly:
```bash
export SIMPLETEST_DB='mysql://db:db@db/db'
```

**Issue**: Tests are slow
**Solution**: Run only unit tests for faster feedback during development

---

## Summary

- **66 tests** covering all major functionality
- **Unit tests** for fast, isolated testing
- **Kernel tests** for integration testing
- **413 assertions** ensuring code quality
- **Easy to extend** with new tests
- **Convenient `./test` script** for quick test execution
- **Centralized configuration** works for all modules

### Quick Reference

```bash
# Most common commands
./test              # Run all tests
./test unit         # Fast unit tests only
./test kernel       # Kernel tests only
./test module <name> # Test specific module
```

To add tests for new pipes or methods:
1. Create test file in appropriate directory
2. Follow naming conventions
3. Use appropriate test base class (UnitTestCase or KernelTestBase)
4. Run tests: `./test module your_module`
5. Aim for 100% code coverage

---

## See Also

- **Getting Started**: [01-getting-started.md](01-getting-started.md)
- **Design Patterns**: [02-patterns.md](02-patterns.md)
- **Code Examples**: [03-examples.md](03-examples.md)
- **API Reference**: [04-api-reference.md](04-api-reference.md)
