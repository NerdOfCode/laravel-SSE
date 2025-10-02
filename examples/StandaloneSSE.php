<?php
/**
 * Standalone SSE Class
 *
 * A simple Server-Sent Events implementation that works without Laravel
 */

class StandaloneSSE
{
    protected int $retry = 3000;
    protected ?string $eventId = null;
    protected array $headers = [];
    protected int $executionTime = 0;
    protected bool $isStreaming = false;

    public function __construct()
    {
        $this->setDefaultHeaders();
    }

    protected function setDefaultHeaders(): void
    {
        $this->headers = [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ];
    }

    public function setRetry(int $milliseconds): self
    {
        $this->retry = $milliseconds;
        return $this;
    }

    public function setEventId(?string $id): self
    {
        $this->eventId = $id;
        return $this;
    }

    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function setExecutionTime(int $seconds): self
    {
        $this->executionTime = $seconds;
        return $this;
    }

    public function stream(callable $callback): void
    {
        $this->isStreaming = true;
        $this->sendHeaders();
        $this->configureEnvironment();
        $this->sendRetry();

        $callback($this);
    }

    protected function sendHeaders(): void
    {
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }
    }

    protected function configureEnvironment(): void
    {
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }

        @ini_set('zlib.output_compression', '0');

        if ($this->executionTime > 0) {
            @set_time_limit($this->executionTime);
        } else {
            @set_time_limit(0);
        }
    }

    protected function sendRetry(): void
    {
        echo "retry: {$this->retry}\n\n";
        $this->flush();
    }

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

    public function message(string $data, ?string $id = null): self
    {
        return $this->event($data, null, $id);
    }

    public function json(array|object $data, ?string $event = null, ?string $id = null): self
    {
        return $this->event(json_encode($data), $event, $id);
    }

    public function comment(string $comment): self
    {
        echo ": {$comment}\n\n";
        $this->flush();
        return $this;
    }

    protected function formatData(string $data): string
    {
        $lines = explode("\n", $data);
        return implode("\ndata: ", $lines);
    }

    protected function flush(): void
    {
        // Only flush when actively streaming (not in unit tests)
        if (!$this->isStreaming) {
            return;
        }

        // Flush all output buffers to send data to client
        if (ob_get_level() > 0) {
            @ob_flush();
        }
        @flush();
    }

    public function isConnected(): bool
    {
        return connection_status() === CONNECTION_NORMAL;
    }

    public function stop(): void
    {
        exit();
    }

    public function create(callable $generator, int $interval = 1): void
    {
        $this->stream(function (StandaloneSSE $sse) use ($generator, $interval) {
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
