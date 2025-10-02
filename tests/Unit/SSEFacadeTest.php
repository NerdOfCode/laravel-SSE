<?php

namespace LaravelSSE\Tests\Unit;

use LaravelSSE\Facades\SSE as SSEFacade;
use LaravelSSE\SSE;
use LaravelSSE\Tests\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SSEFacadeTest extends TestCase
{
    public function test_facade_resolves_to_sse_instance(): void
    {
        $sse = SSEFacade::getFacadeRoot();

        $this->assertInstanceOf(SSE::class, $sse);
    }

    public function test_facade_set_retry_returns_sse_instance(): void
    {
        $result = SSEFacade::setRetry(5000);

        $this->assertInstanceOf(SSE::class, $result);
    }

    public function test_facade_set_event_id_returns_sse_instance(): void
    {
        $result = SSEFacade::setEventId('test-id');

        $this->assertInstanceOf(SSE::class, $result);
    }

    public function test_facade_set_header_returns_sse_instance(): void
    {
        $result = SSEFacade::setHeader('X-Test', 'value');

        $this->assertInstanceOf(SSE::class, $result);
    }

    public function test_facade_set_execution_time_returns_sse_instance(): void
    {
        $result = SSEFacade::setExecutionTime(300);

        $this->assertInstanceOf(SSE::class, $result);
    }

    public function test_facade_stream_returns_streamed_response(): void
    {
        $response = SSEFacade::stream(function (SSE $sse) {
            // Immediately stop
        });

        $this->assertInstanceOf(StreamedResponse::class, $response);
    }

    public function test_facade_create_returns_streamed_response(): void
    {
        $response = SSEFacade::create(function () {
            return false;
        });

        $this->assertInstanceOf(StreamedResponse::class, $response);
    }

    public function test_facade_is_connected_returns_boolean(): void
    {
        $this->assertIsBool(SSEFacade::isConnected());
    }

    public function test_facade_can_chain_methods(): void
    {
        $response = SSEFacade::setRetry(5000)
            ->setEventId('chain-test')
            ->stream(function (SSE $sse) {
                // Immediately stop
            });

        $this->assertInstanceOf(StreamedResponse::class, $response);
    }

    public function test_facade_resolves_to_same_instance_as_container(): void
    {
        $facadeInstance = SSEFacade::getFacadeRoot();
        $containerInstance = app('sse');

        $this->assertSame($facadeInstance, $containerInstance);
    }
}
