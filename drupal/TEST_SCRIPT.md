# Test Script Documentation

## Quick Start

Run PHPUnit tests easily with the `./test` script:

```bash
./test              # Run all custom module tests
./test unit         # Run unit tests only (fast)
./test kernel       # Run kernel tests only
./test help         # Show all available commands
```

## All Commands

| Command | Description | Example |
|---------|-------------|---------|
| `./test` | Run all custom module tests | `./test` |
| `./test unit` or `./test u` | Run unit tests (fast, no DB) | `./test unit` |
| `./test kernel` or `./test k` | Run kernel tests (with DB) | `./test kernel` |
| `./test functional` or `./test f` | Run functional tests | `./test functional` |
| `./test module <name>` | Test specific module | `./test module boxuk_patterns` |
| `./test file <path>` | Test specific file | `./test file modules/boxuk_patterns/tests/src/Unit/Pipe/PipelineTest.php` |
| `./test filter <pattern>` | Test specific method(s) | `./test filter testHandleReturnsTitleData` |
| `./test coverage` | Generate coverage report | `./test coverage` |
| `./test help` | Show help message | `./test help` |

## Features

- ✅ **Auto-starts DDEV** if not running
- ✅ **Colored output** for better readability
- ✅ **Smart path handling** - removes `web/` prefix automatically
- ✅ **Custom test suites** - excludes Drupal core tests by default
- ✅ **Coverage reports** - generate HTML coverage with one command

## Examples

### Run tests for a specific module

```bash
./test module boxuk_patterns
```

### Run a specific test file

```bash
# With or without 'web/' prefix - both work
./test file web/modules/boxuk_patterns/tests/src/Unit/Pipe/PipelineTest.php
./test file modules/boxuk_patterns/tests/src/Unit/Pipe/PipelineTest.php
```

### Run specific test method

```bash
./test filter testHandleReturnsTitleData
```

### Generate coverage report

```bash
./test coverage
# Opens coverage/index.html when complete
```

## Configuration

The script uses the centralized `web/phpunit.xml` configuration, which:

- Pre-configured for DDEV environment
- Works for all custom modules
- Includes custom test suites that exclude Drupal core
- No per-module configuration needed

## Test Suites

The script runs tests from these custom suites by default:

- `custom-unit` - Unit tests from all custom modules
- `custom-kernel` - Kernel tests from all custom modules
- `custom-functional` - Functional tests from all custom modules

These suites **exclude Drupal core tests**, making test runs faster and more relevant to your custom code.

## Requirements

- DDEV environment
- PHPUnit (installed via `drupal/core-dev`)

Install test dependencies:

```bash
ddev composer require --dev "drupal/core-dev:^10"
```

## Troubleshooting

**Issue**: Script says "DDEV is not installed"
**Solution**: Install DDEV or ensure it's in your PATH

**Issue**: Script says "DDEV is not running"
**Solution**: The script will auto-start DDEV. If it fails, run `ddev start` manually

**Issue**: Tests not found
**Solution**: Ensure your test files follow Drupal conventions:
- Located in `modules/*/tests/src/Unit` or `modules/*/tests/src/Kernel`
- End with `Test.php`
- Extend `UnitTestCase` or `KernelTestBase`

## See Also

- Full testing documentation: `web/modules/boxuk_patterns/docs/05-testing.md`
- PHPUnit configuration: `web/phpunit.xml`
- Drupal testing guide: https://www.drupal.org/docs/testing
