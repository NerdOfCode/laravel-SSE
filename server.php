<?php
/**
 * Standalone PHP Server for SSE Examples
 *
 * Run with: php -S localhost:8000 server.php
 * Then visit: http://localhost:8000
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the request URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static files
if ($uri !== '/' && file_exists(__DIR__ . '/examples' . $uri)) {
    return false;
}

// Load the standalone SSE class
require_once __DIR__ . '/examples/StandaloneSSE.php';

// Route handler
switch ($uri) {
    case '/':
    case '/index.html':
        header('Content-Type: text/html');
        readfile(__DIR__ . '/examples/client.html');
        break;

    case '/counter':
        $counter = 0;
        $sse = new StandaloneSSE();
        $sse->create(function () use (&$counter) {
            $counter++;
            if ($counter > 20) {
                return false;
            }
            return [
                'count' => $counter,
                'time' => date('Y-m-d H:i:s')
            ];
        }, 1);
        break;

    case '/progress':
        $sse = new StandaloneSSE();
        $sse->stream(function ($sse) {
            $steps = ['Initializing', 'Processing', 'Validating', 'Finalizing', 'Complete'];

            foreach ($steps as $index => $step) {
                if (!$sse->isConnected()) {
                    break;
                }

                $progress = (($index + 1) / count($steps)) * 100;

                $sse->json([
                    'step' => $step,
                    'progress' => round($progress, 2),
                    'message' => "Step " . ($index + 1) . ": {$step}"
                ], 'progress');

                sleep(2);
            }

            $sse->json(['complete' => true], 'done');
        });
        break;

    case '/clock':
        $sse = new StandaloneSSE();
        $sse->stream(function ($sse) {
            while ($sse->isConnected()) {
                $sse->json([
                    'time' => date('H:i:s'),
                    'date' => date('Y-m-d'),
                    'timestamp' => time()
                ], 'clock');
                sleep(1);
            }
        });
        break;

    case '/random':
        $sse = new StandaloneSSE();
        $sse->stream(function ($sse) {
            $count = 0;
            while ($sse->isConnected() && $count < 30) {
                $count++;

                $sse->json([
                    'number' => rand(1, 100),
                    'count' => $count
                ], 'random');

                sleep(1);
            }
        });
        break;

    case '/messages':
        $sse = new StandaloneSSE();
        $sse->stream(function ($sse) {
            $messages = [
                'Hello, World!',
                'Server-Sent Events are awesome!',
                'Real-time updates without WebSockets',
                'Simple and efficient',
                'Perfect for one-way communication'
            ];

            foreach ($messages as $index => $message) {
                if (!$sse->isConnected()) {
                    break;
                }

                $sse->json([
                    'message' => $message,
                    'index' => $index + 1,
                    'total' => count($messages)
                ], 'message', 'msg-' . ($index + 1));

                sleep(2);
            }
        });
        break;

    case '/server-stats':
        $sse = new StandaloneSSE();
        $sse->stream(function ($sse) {
            while ($sse->isConnected()) {
                $sse->json([
                    'timestamp' => date('Y-m-d H:i:s'),
                    'memory' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
                    'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB',
                    'uptime' => time() - $_SERVER['REQUEST_TIME'],
                ], 'stats');

                sleep(3);
            }
        });
        break;

    default:
        http_response_code(404);
        echo "404 - Not Found\n\n";
        echo "Available endpoints:\n";
        echo "  /               - HTML client\n";
        echo "  /counter        - Simple counter\n";
        echo "  /progress       - Progress monitor\n";
        echo "  /clock          - Real-time clock\n";
        echo "  /random         - Random numbers\n";
        echo "  /messages       - Message stream\n";
        echo "  /server-stats   - Server statistics\n";
        break;
}
