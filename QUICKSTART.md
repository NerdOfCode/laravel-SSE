# Quick Start Guide

## ⚡ Fastest Way: Run the Standalone Server

**No Laravel installation needed!**

1. **Start the server:**
```bash
cd /Users/wk/Desktop/Projects/Laravel/SSE
php -S localhost:8000 server.php
```

2. **Open your browser:**
```
http://localhost:8000
```

3. **Try the examples:**
- Click any of the Quick Examples buttons
- Or test with curl: `curl -N http://localhost:8000/counter`

**Available endpoints:**
- `/counter` - Simple counter (1-20)
- `/progress` - Progress bar simulation
- `/clock` - Real-time clock
- `/random` - Random number generator
- `/messages` - Sequential messages
- `/server-stats` - Server statistics

---

# Quick Start Guide (Laravel Integration)

## Option 1: Install in Existing Laravel App

1. **Add to composer.json** in your Laravel app:
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../SSE"
        }
    ],
    "require": {
        "nerdofcode/laravel-sse": "*"
    }
}
```

2. **Install the package:**
```bash
composer require nerdofcode/laravel-sse
```

3. **Add routes** to `routes/web.php`:
```php
use LaravelSSE\Facades\SSE;

Route::get('/stream', function () {
    return SSE::create(function () {
        static $counter = 0;
        return ['count' => ++$counter, 'time' => now()->toDateTimeString()];
    }, 1);
});
```

4. **Start Laravel server:**
```bash
php artisan serve
```

5. **Test it:**
- Visit: http://localhost:8000/stream (you'll see raw SSE data)
- Or use the HTML client (copy `examples/client.html` to `public/sse-client.html`)
- Visit: http://localhost:8000/sse-client.html

## Option 2: Standalone Test (Without Laravel App)

1. **Create a test Laravel app:**
```bash
cd /Users/wk/Desktop/Projects/Laravel
composer create-project laravel/laravel test-sse
cd test-sse
```

2. **Add repository** to `composer.json`:
```json
"repositories": [
    {
        "type": "path",
        "url": "../SSE"
    }
]
```

3. **Install:**
```bash
composer require nerdofcode/laravel-sse
```

4. **Copy example files:**
```bash
cp ../SSE/examples/ExampleController.php app/Http/Controllers/
cp ../SSE/examples/client.html public/sse-client.html
```

5. **Add routes** to `routes/web.php`:
```php
use App\Http\Controllers\ExampleController;

Route::get('/simple', [ExampleController::class, 'simpleCounter']);
Route::get('/progress', [ExampleController::class, 'progress']);
Route::get('/custom', [ExampleController::class, 'customStream']);

Route::get('/sse-client', function () {
    return view('sse-client');
});
```

6. **Run:**
```bash
php artisan serve
```

7. **Access:**
- http://localhost:8000/simple
- http://localhost:8000/progress
- http://localhost:8000/sse-client (GUI client)

## Option 3: Quick curl Test

```bash
# In one terminal, start server
php artisan serve

# In another terminal, test with curl
curl -N http://localhost:8000/stream
```

## JavaScript Client Test

Create `test.html` anywhere:
```html
<!DOCTYPE html>
<html>
<body>
    <div id="output"></div>
    <script>
        const es = new EventSource('http://localhost:8000/stream');
        es.onmessage = e => {
            document.getElementById('output').innerHTML += e.data + '<br>';
        };
    </script>
</body>
</html>
```

Open in browser and watch events stream in!
