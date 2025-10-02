# Tests

This directory contains the test suite for the Laravel SSE library.

## Structure

```
tests/
├── TestCase.php           # Base test case for Laravel integration tests
├── Unit/                  # Unit tests
│   ├── SSETest.php               # Tests for the main SSE class
│   ├── SSEServiceProviderTest.php # Tests for the service provider
│   ├── SSEFacadeTest.php          # Tests for the facade
│   └── StandaloneSSETest.php      # Tests for standalone SSE class
└── Feature/               # Feature/Integration tests
    └── SSEIntegrationTest.php     # Integration tests
```

## Running Tests

### Install Dependencies

First, install the dev dependencies:

```bash
composer install
```

### Run All Tests

```bash
composer test
# or
./vendor/bin/phpunit
```

### Run Specific Test Suite

```bash
# Unit tests only
./vendor/bin/phpunit tests/Unit

# Feature tests only
./vendor/bin/phpunit tests/Feature

# Specific test file
./vendor/bin/phpunit tests/Unit/SSETest.php
```

### Run with Coverage

```bash
composer test-coverage
```

This will generate an HTML coverage report in `build/coverage/index.html`.

### Run Specific Test Method

```bash
./vendor/bin/phpunit --filter test_can_instantiate_sse_class
```

## Test Coverage

The test suite covers:

- ✅ SSE class instantiation and configuration
- ✅ Event sending (data, JSON, comments)
- ✅ Stream creation and headers
- ✅ Service provider registration
- ✅ Facade functionality
- ✅ Standalone SSE implementation
- ✅ Integration tests for full streaming workflow
- ✅ Method chaining
- ✅ Output formatting

## Writing New Tests

### Unit Tests

Place unit tests in `tests/Unit/`. Unit tests should test individual components in isolation.

```php
<?php

namespace LaravelSSE\Tests\Unit;

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    public function test_example(): void
    {
        $this->assertTrue(true);
    }
}
```

### Feature Tests

Place feature/integration tests in `tests/Feature/`. Use the `TestCase` base class for Laravel integration:

```php
<?php

namespace LaravelSSE\Tests\Feature;

use LaravelSSE\Tests\TestCase;

class MyIntegrationTest extends TestCase
{
    public function test_example(): void
    {
        $this->assertTrue(true);
    }
}
```

## Requirements

- PHP >= 8.0
- PHPUnit ^9.5|^10.0
- Orchestra Testbench ^7.0|^8.0|^9.0 (for Laravel integration tests)
- Mockery ^1.5
