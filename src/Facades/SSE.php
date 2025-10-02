<?php

namespace LaravelSSE\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \LaravelSSE\SSE setRetry(int $milliseconds)
 * @method static \LaravelSSE\SSE setEventId(?string $id)
 * @method static \LaravelSSE\SSE setHeader(string $key, string $value)
 * @method static \LaravelSSE\SSE setExecutionTime(int $seconds)
 * @method static \Symfony\Component\HttpFoundation\StreamedResponse stream(callable $callback)
 * @method static \LaravelSSE\SSE event(string $data, ?string $event = null, ?string $id = null)
 * @method static \LaravelSSE\SSE message(string $data, ?string $id = null)
 * @method static \LaravelSSE\SSE json(array|object $data, ?string $event = null, ?string $id = null)
 * @method static \LaravelSSE\SSE comment(string $comment)
 * @method static bool isConnected()
 * @method static void stop()
 * @method static \Symfony\Component\HttpFoundation\StreamedResponse create(callable $generator, int $interval = 1)
 *
 * @see \LaravelSSE\SSE
 */
class SSE extends Facade
{
    /**
     * Get the registered name of the component
     */
    protected static function getFacadeAccessor(): string
    {
        return 'sse';
    }
}
