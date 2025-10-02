<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LaravelSSE\Facades\SSE;

class ExampleController extends Controller
{
    /**
     * Example 1: Simple counter using create method
     */
    public function simpleCounter()
    {
        $counter = 0;

        return SSE::create(function () use (&$counter) {
            $counter++;

            if ($counter > 10) {
                return false; // Stop streaming
            }

            return ['count' => $counter, 'time' => now()->toDateTimeString()];
        }, 1); // Send every 1 second
    }

    /**
     * Example 2: Custom stream with event types
     */
    public function customStream()
    {
        return SSE::stream(function (SSE $sse) {
            $counter = 0;

            while ($sse->isConnected() && $counter < 20) {
                $counter++;

                // Send different event types
                if ($counter % 5 === 0) {
                    $sse->json(['count' => $counter], 'milestone', "msg-{$counter}");
                } else {
                    $sse->json(['count' => $counter], 'update', "msg-{$counter}");
                }

                // Send a comment every 10 iterations to keep connection alive
                if ($counter % 10 === 0) {
                    $sse->comment('Keep-alive ping');
                }

                sleep(1);
            }
        });
    }

    /**
     * Example 3: Real-time data from database
     */
    public function liveData()
    {
        return app('sse')->stream(function (SSE $sse) {
            $lastId = 0;

            while ($sse->isConnected()) {
                // Fetch new records from database
                $newRecords = \App\Models\YourModel::where('id', '>', $lastId)
                    ->orderBy('id')
                    ->limit(10)
                    ->get();

                foreach ($newRecords as $record) {
                    $sse->json($record->toArray(), 'new-record', $record->id);
                    $lastId = $record->id;
                }

                // If no new data, send keep-alive comment
                if ($newRecords->isEmpty()) {
                    $sse->comment('Waiting for new data...');
                }

                sleep(2);
            }
        });
    }

    /**
     * Example 4: Progress monitoring
     */
    public function progress()
    {
        return SSE::setRetry(5000)->stream(function (SSE $sse) {
            $steps = ['Initializing', 'Processing', 'Validating', 'Finalizing', 'Complete'];

            foreach ($steps as $index => $step) {
                if (!$sse->isConnected()) {
                    break;
                }

                $progress = (($index + 1) / count($steps)) * 100;

                $sse->json([
                    'step' => $step,
                    'progress' => $progress,
                    'message' => "Step {$index}: {$step}"
                ], 'progress');

                sleep(2);
            }

            $sse->json(['complete' => true], 'done');
        });
    }

    /**
     * Example 5: Server status monitoring
     */
    public function serverStatus()
    {
        return SSE::stream(function (SSE $sse) {
            while ($sse->isConnected()) {
                $status = [
                    'timestamp' => now()->toDateTimeString(),
                    'memory' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
                    'cpu_load' => sys_getloadavg(),
                    'connections' => rand(100, 500), // Replace it with actual metric
                ];

                $sse->json($status, 'status');

                sleep(5);
            }
        });
    }

    /**
     * Example 6: Chat/notifications stream
     */
    public function notifications(Request $request)
    {
        $userId = $request->user()->id;

        return SSE::stream(function (SSE $sse) use ($userId) {
            $lastCheck = now();

            while ($sse->isConnected()) {
                // Check for new notifications
                $notifications = \App\Models\Notification::where('user_id', $userId)
                    ->where('created_at', '>', $lastCheck)
                    ->get();

                foreach ($notifications as $notification) {
                    $sse->json([
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'message' => $notification->message,
                        'created_at' => $notification->created_at,
                    ], 'notification', $notification->id);
                }

                $lastCheck = now();
                sleep(1);
            }
        });
    }

    /**
     * Example 7: Using dependency injection
     */
    public function withDependencyInjection(SSE $sse)
    {
        return $sse->create(function () {
            return [
                'time' => now()->toDateTimeString(),
                'random' => rand(1, 100),
            ];
        });
    }
}
