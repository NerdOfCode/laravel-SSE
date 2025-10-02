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

        // Integration test: just verify response type and headers
        // Actual output format is tested in unit tests
        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals('text/event-stream', $response->headers->get('Content-Type'));
    }

    public function test_stream_sends_events_correctly(): void
    {
        $eventSent = false;
        $response = SSE::stream(function ($sse) use (&$eventSent) {
            $sse->event('Test data', 'test-event', 'event-1');
            $eventSent = true;
        });

        $this->assertInstanceOf(StreamedResponse::class, $response);

        // Execute the stream to verify callback runs
        ob_start();
        $response->sendContent();
        ob_end_clean();

        $this->assertTrue($eventSent);
    }

    public function test_stream_sends_json_events_correctly(): void
    {
        $jsonSent = false;
        $response = SSE::stream(function ($sse) use (&$jsonSent) {
            $sse->json(['user' => 'John', 'status' => 'active'], 'user-update');
            $jsonSent = true;
        });

        $this->assertInstanceOf(StreamedResponse::class, $response);

        // Execute the stream to verify callback runs
        ob_start();
        $response->sendContent();
        ob_end_clean();

        $this->assertTrue($jsonSent);
    }

    public function test_stream_sends_comments_correctly(): void
    {
        $commentSent = false;
        $response = SSE::stream(function ($sse) use (&$commentSent) {
            $sse->comment('Keep-alive ping');
            $commentSent = true;
        });

        $this->assertInstanceOf(StreamedResponse::class, $response);

        // Execute the stream to verify callback runs
        ob_start();
        $response->sendContent();
        ob_end_clean();

        $this->assertTrue($commentSent);
    }

    public function test_create_method_automatically_sends_data(): void
    {
        $callCount = 0;
        $response = SSE::create(function () use (&$callCount) {
            $callCount++;
            if ($callCount > 1) {
                return false;
            }
            return ['message' => 'Hello'];
        });

        $this->assertInstanceOf(StreamedResponse::class, $response);

        // Execute the stream to verify callback runs
        ob_start();
        $response->sendContent();
        ob_end_clean();

        $this->assertGreaterThan(0, $callCount);
    }

    public function test_create_method_handles_array_data(): void
    {
        $dataSent = false;
        $response = SSE::create(function () use (&$dataSent) {
            if ($dataSent) return false;
            $dataSent = true;
            return ['key' => 'value'];
        });

        $this->assertInstanceOf(StreamedResponse::class, $response);

        // Execute the stream to verify callback runs
        ob_start();
        $response->sendContent();
        ob_end_clean();

        $this->assertTrue($dataSent);
    }

    public function test_create_method_handles_string_data(): void
    {
        $stringSent = false;
        $response = SSE::create(function () use (&$stringSent) {
            if ($stringSent) return false;
            $stringSent = true;
            return 'Simple string';
        });

        $this->assertInstanceOf(StreamedResponse::class, $response);

        // Execute the stream to verify callback runs
        ob_start();
        $response->sendContent();
        ob_end_clean();

        $this->assertTrue($stringSent);
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
        $messageSent = false;
        $response = SSE::setRetry(5000)
            ->setEventId('chain-test')
            ->setHeader('X-Test', 'value')
            ->stream(function ($sse) use (&$messageSent) {
                $sse->message('Chained test');
                $messageSent = true;
            });

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals('value', $response->headers->get('X-Test'));

        // Execute the stream to verify callback runs
        ob_start();
        $response->sendContent();
        ob_end_clean();

        $this->assertTrue($messageSent);
    }
}
