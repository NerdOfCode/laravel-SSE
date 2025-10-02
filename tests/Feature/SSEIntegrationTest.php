<?php

namespace LaravelSSE\Tests\Feature;

use LaravelSSE\Facades\SSE;
use LaravelSSE\Tests\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SSEIntegrationTest extends TestCase
{
    public function test_can_create_sse_stream_via_facade(): void
    {
        $response = SSE::stream(function ($sse) {
            $sse->message('Test message');
        });

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_can_create_sse_stream_via_helper(): void
    {
        $response = app('sse')->stream(function ($sse) {
            $sse->message('Test message');
        });

        $this->assertInstanceOf(StreamedResponse::class, $response);
    }

    public function test_can_create_simple_stream_with_create_method(): void
    {
        $response = SSE::create(function () {
            static $count = 0;
            if ($count++ > 2) {
                return false;
            }
            return ['count' => $count];
        });

        $this->assertInstanceOf(StreamedResponse::class, $response);
    }

    public function test_stream_output_contains_retry_instruction(): void
    {
        $response = SSE::setRetry(5000)->stream(function ($sse) {
            // Stop immediately
        });

        ob_start();
        $response->sendContent();
        $output = ob_get_clean();

        $this->assertStringContainsString('retry: 5000', $output);
    }

    public function test_stream_sends_events_correctly(): void
    {
        $response = SSE::stream(function ($sse) {
            $sse->event('Test data', 'test-event', 'event-1');
        });

        ob_start();
        $response->sendContent();
        $output = ob_get_clean();

        $this->assertStringContainsString('event: test-event', $output);
        $this->assertStringContainsString('data: Test data', $output);
        $this->assertStringContainsString('id: event-1', $output);
    }

    public function test_stream_sends_json_events_correctly(): void
    {
        $response = SSE::stream(function ($sse) {
            $sse->json(['user' => 'John', 'status' => 'active'], 'user-update');
        });

        ob_start();
        $response->sendContent();
        $output = ob_get_clean();

        $this->assertStringContainsString('event: user-update', $output);
        $this->assertStringContainsString('"user":"John"', $output);
        $this->assertStringContainsString('"status":"active"', $output);
    }

    public function test_stream_sends_comments_correctly(): void
    {
        $response = SSE::stream(function ($sse) {
            $sse->comment('Keep-alive ping');
        });

        ob_start();
        $response->sendContent();
        $output = ob_get_clean();

        $this->assertStringContainsString(': Keep-alive ping', $output);
    }

    public function test_create_method_automatically_sends_data(): void
    {
        $response = SSE::create(function () {
            static $sent = false;
            if ($sent) {
                return false;
            }
            $sent = true;
            return ['message' => 'Hello'];
        });

        ob_start();
        $response->sendContent();
        $output = ob_get_clean();

        $this->assertStringContainsString('"message":"Hello"', $output);
    }

    public function test_create_method_handles_array_data(): void
    {
        $response = SSE::create(function () {
            static $sent = false;
            if ($sent) return false;
            $sent = true;
            return ['key' => 'value'];
        });

        ob_start();
        $response->sendContent();
        $output = ob_get_clean();

        $this->assertStringContainsString('"key":"value"', $output);
    }

    public function test_create_method_handles_string_data(): void
    {
        $response = SSE::create(function () {
            static $sent = false;
            if ($sent) return false;
            $sent = true;
            return 'Simple string';
        });

        ob_start();
        $response->sendContent();
        $output = ob_get_clean();

        $this->assertStringContainsString('data: Simple string', $output);
    }

    public function test_custom_headers_are_applied(): void
    {
        $response = SSE::setHeader('X-Custom-Header', 'custom-value')
            ->stream(function ($sse) {
                // Stop immediately
            });

        $this->assertEquals('custom-value', $response->headers->get('X-Custom-Header'));
    }

    public function test_method_chaining_works_correctly(): void
    {
        $response = SSE::setRetry(5000)
            ->setEventId('chain-test')
            ->setHeader('X-Test', 'value')
            ->stream(function ($sse) {
                $sse->message('Chained test');
            });

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals('value', $response->headers->get('X-Test'));

        ob_start();
        $response->sendContent();
        $output = ob_get_clean();

        $this->assertStringContainsString('retry: 5000', $output);
    }
}
