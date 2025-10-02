<?php

namespace LaravelSSE;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SSE
{
    protected int $retry = 3000;
    protected ?string $eventId = null;
    protected array $headers = [];
    protected int $executionTime = 0;

    /**
     * Create a new SSE instance
     */
    public function __construct()
    {
        $this->setDefaultHeaders();
    }

    /**
     * Set default headers for SSE
     */
    protected function setDefaultHeaders(): void
    {
        $this->headers = [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ];
    }

    /**
     * Set retry time in milliseconds
     */
    public function setRetry(int $milliseconds): self
    {
        $this->retry = $milliseconds;
        return $this;
    }

    /**
     * Set event ID
     */
    public function setEventId(?string $id): self
    {
        $this->eventId = $id;
        return $this;
    }

    /**
     * Set custom header
     */
    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Set maximum execution time (0 for unlimited)
     */
    public function setExecutionTime(int $seconds): self
    {
        $this->executionTime = $seconds;
        return $this;
    }

    /**
     * Create a streamed response with SSE
     */
    public function stream(callable $callback): StreamedResponse
    {
        return new StreamedResponse(function () use ($callback) {
            $this->configureEnvironment();
            $this->sendRetry();

            $callback($this);
        }, 200, $this->headers);
    }

    /**
     * Configure PHP environment for SSE
     */
    protected function configureEnvironment(): void
    {
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
        }

        ini_set('zlib.output_compression', '0');
        ini_set('implicit_flush', '1');

        if ($this->executionTime > 0) {
            set_time_limit($this->executionTime);
        } else {
            set_time_limit(0);
        }

        ob_end_flush();
        ob_implicit_flush(true);
    }

    /**
     * Send retry instruction to client
     */
    protected function sendRetry(): void
    {
        echo "retry: {$this->retry}\n\n";
        $this->flush();
    }

    /**
     * Send an event
     */
    public function event(string $data, ?string $event = null, ?string $id = null): self
    {
        if ($id !== null || $this->eventId !== null) {
            echo "id: " . ($id ?? $this->eventId) . "\n";
        }

        if ($event !== null) {
            echo "event: {$event}\n";
        }

        $data = $this->formatData($data);
        echo "data: {$data}\n\n";

        $this->flush();

        return $this;
    }

    /**
     * Send a message (alias for event with data only)
     */
    public function message(string $data, ?string $id = null): self
    {
        return $this->event($data, null, $id);
    }

    /**
     * Send a JSON event
     */
    public function json(array|object $data, ?string $event = null, ?string $id = null): self
    {
        return $this->event(json_encode($data), $event, $id);
    }

    /**
     * Send a comment (for keeping connection alive)
     */
    public function comment(string $comment): self
    {
        echo ": {$comment}\n\n";
        $this->flush();
        return $this;
    }

    /**
     * Format data for SSE (handle multiline)
     */
    protected function formatData(string $data): string
    {
        $lines = explode("\n", $data);
        return implode("\ndata: ", $lines);
    }

    /**
     * Flush output buffer
     */
    protected function flush(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }

    /**
     * Check if client is still connected
     */
    public function isConnected(): bool
    {
        return connection_status() === CONNECTION_NORMAL;
    }

    /**
     * Stop the SSE stream
     */
    public function stop(): void
    {
        exit();
    }

    /**
     * Create a simple SSE response with automatic event sending
     */
    public function create(callable $generator, int $interval = 1): StreamedResponse
    {
        return $this->stream(function (SSE $sse) use ($generator, $interval) {
            while ($sse->isConnected()) {
                $data = $generator();

                if ($data === false || $data === null) {
                    break;
                }

                if (is_array($data) || is_object($data)) {
                    $sse->json($data);
                } else {
                    $sse->message((string) $data);
                }

                sleep($interval);
            }
        });
    }
}
