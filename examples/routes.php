<?php

use App\Http\Controllers\ExampleController;
use LaravelSSE\Facades\SSE;

/*
|--------------------------------------------------------------------------
| SSE Routes Example
|--------------------------------------------------------------------------
|
| Add these routes to your routes/web.php or routes/api.php file
|
*/

// Simple inline routes
Route::get('/sse/counter', function () {
    return SSE::create(function () {
        static $counter = 0;
        return ['count' => ++$counter, 'time' => now()->toDateTimeString()];
    }, 1);
});

Route::get('/sse/time', function () {
    return SSE::stream(function ($sse) {
        while ($sse->isConnected()) {
            $sse->json(['time' => now()->toDateTimeString()]);
            sleep(1);
        }
    });
});

// Controller-based routes (from ExampleController)
Route::prefix('sse')->group(function () {
    Route::get('/simple-counter', [ExampleController::class, 'simpleCounter']);
    Route::get('/custom-stream', [ExampleController::class, 'customStream']);
    Route::get('/live-data', [ExampleController::class, 'liveData']);
    Route::get('/progress', [ExampleController::class, 'progress']);
    Route::get('/server-status', [ExampleController::class, 'serverStatus']);

    // Protected route (requires authentication)
    Route::middleware('auth')->get('/notifications', [ExampleController::class, 'notifications']);
});

// API routes (for API-based SSE)
Route::prefix('api/sse')->middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [ExampleController::class, 'notifications']);
    Route::get('/live-data', [ExampleController::class, 'liveData']);
});

// Serve the HTML client example
Route::get('/sse-client', function () {
    return response()->file(base_path('examples/client.html'));
});
