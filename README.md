# Laravel SSE (Server-Sent Events)

A simple Server-Sent Events (SSE) library for Laravel apps.

## 🚀 Quick Start

**Try it now without installing Laravel:**

```bash
./start.sh
# Or manually: php -S localhost:8000 server.php
```

Then open http://localhost:8000 in your browser!

See [QUICKSTART.md](QUICKSTART.md) for more details.

## Features

- Easy integration with Laravel
- Fluent API for sending events
- Support for event types and IDs
- Auto-reconnection handling
- Connection status monitoring
- JSON and text event support
- Keep-alive comments
- Configurable retry times
- Multiple usage patterns

## Installation

Install via Composer:

```bash
composer require nerdofcode/laravel-sse
```

The package will automatically register its service provider.

### Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=sse-config
```

This will create `config/sse.php` where you can configure default settings.

## Basic Usage

### Method 1: Using the Facade

```php
use LaravelSSE\Facades\SSE;

Route::get('/stream', function () {
    return SSE::create(function () {
        return ['time' => now()->toDateTimeString()];
    }, 1); // Sends event every 1 second
});
```

### Method 2: Using Dependency Injection

```php
use LaravelSSE\SSE;

Route::get('/stream', function (SSE $sse) {
    return $sse->stream(function (SSE $sse) {
        $counter = 0;

        while ($sse->isConnected() && $counter < 10) {
            $sse->json(['count' => ++$counter]);
            sleep(1);
        }
    });
});
```

### Method 3: Using the Helper

```php
Route::get('/stream', function () {
    return app('sse')->stream(function ($sse) {
        $sse->message('Hello World!');
    });
});
```

## API Reference

### Creating Streams

#### `stream(callable $callback)`
Create a custom SSE stream with full control:

```php
SSE::stream(function (SSE $sse) {
    while ($sse->isConnected()) {
        $sse->event('data', 'event-name', 'event-id');
        sleep(1);
    }
});
```

#### `create(callable $generator, int $interval = 1)`
Create a simple auto-streaming SSE response:

```php
SSE::create(function () {
    return ['data' => 'value'];
}, 2); // Send every 2 seconds
```

### Sending Events

#### `event(string $data, ?string $event = null, ?string $id = null)`
Send a custom event:

```php
$sse->event('Message content', 'message', '123');
```

#### `message(string $data, ?string $id = null)`
Send a simple message (alias for event with data only):

```php
$sse->message('Hello World', '123');
```

#### `json(array|object $data, ?string $event = null, ?string $id = null)`
Send JSON data:

```php
$sse->json(['user' => 'John', 'status' => 'active'], 'user-update', '456');
```

#### `comment(string $comment)`
Send a comment (keeps connection alive):

```php
$sse->comment('Keep-alive');
```

### Configuration Methods

#### `setRetry(int $milliseconds)`
Set reconnection retry time:

```php
SSE::setRetry(5000)->stream(...);
```

#### `setEventId(?string $id)`
Set default event ID:

```php
SSE::setEventId('stream-1')->stream(...);
```

#### `setHeader(string $key, string $value)`
Add custom header:

```php
SSE::setHeader('X-Custom', 'value')->stream(...);
```

#### `setExecutionTime(int $seconds)`
Set maximum execution time (0 = unlimited):

```php
SSE::setExecutionTime(300)->stream(...);
```

### Utility Methods

#### `isConnected()`
Check if client is still connected:

```php
while ($sse->isConnected()) {
    // Send events
}
```

#### `stop()`
Stop the stream:

```php
$sse->stop();
```

## Client-Side (JavaScript)

```javascript
const eventSource = new EventSource('/stream');

// Listen to all messages
eventSource.onmessage = (event) => {
    const data = JSON.parse(event.data);
    console.log('Received:', data);
};

// Listen to specific event types
eventSource.addEventListener('custom-event', (event) => {
    const data = JSON.parse(event.data);
    console.log('Custom event:', data);
});

// Handle errors
eventSource.onerror = (error) => {
    console.error('SSE Error:', error);
};

// Close connection
eventSource.close();
```

## Examples

### Real-time Counter

```php
Route::get('/counter', function () {
    return SSE::create(function () use (&$counter) {
        static $counter = 0;
        return ['count' => ++$counter];
    });
});
```

### Progress Monitor

```php
Route::get('/progress', function () {
    return SSE::stream(function (SSE $sse) {
        for ($i = 0; $i <= 100; $i += 10) {
            $sse->json([
                'progress' => $i,
                'status' => $i < 100 ? 'processing' : 'complete'
            ], 'progress');
            sleep(1);
        }
    });
});
```

### Live Notifications

```php
Route::get('/notifications', function (Request $request) {
    $userId = $request->user()->id;

    return SSE::stream(function (SSE $sse) use ($userId) {
        $lastCheck = now();

        while ($sse->isConnected()) {
            $notifications = Notification::where('user_id', $userId)
                ->where('created_at', '>', $lastCheck)
                ->get();

            foreach ($notifications as $notification) {
                $sse->json($notification, 'notification', $notification->id);
            }

            $lastCheck = now();
            sleep(2);
        }
    });
});
```

## Testing

The package includes a comprehensive test suite.

### Running Tests

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Run with coverage report
composer test-coverage

# Run specific test suite
./vendor/bin/phpunit tests/Unit
./vendor/bin/phpunit tests/Feature
```

### Test Coverage

- ✅ SSE class methods and configuration
- ✅ Event formatting and output
- ✅ Service provider registration
- ✅ Facade functionality
- ✅ Integration tests for streaming
- ✅ Standalone SSE implementation

See [tests/README.md](tests/README.md) for more details.

## Configuration

Edit `config/sse.php`:

```php
return [
    'retry' => env('SSE_RETRY', 3000), // Retry time in ms
    'execution_time' => env('SSE_EXECUTION_TIME', 0), // Max execution time
];
```

Or use `.env`:

```env
SSE_RETRY=5000
SSE_EXECUTION_TIME=300
```

## Requirements

- PHP >= 8.0
- Laravel >= 9.0

## License

MIT License
