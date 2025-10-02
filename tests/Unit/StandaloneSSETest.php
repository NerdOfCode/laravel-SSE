<?php

namespace LaravelSSE\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../examples/StandaloneSSE.php';

class StandaloneSSETest extends TestCase
{
    protected \StandaloneSSE $sse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sse = new \StandaloneSSE();
    }

    public function test_can_instantiate_standalone_sse_class(): void
    {
        $this->assertInstanceOf(\StandaloneSSE::class, $this->sse);
    }

    public function test_can_set_retry_time(): void
    {
        $result = $this->sse->setRetry(5000);

        $this->assertInstanceOf(\StandaloneSSE::class, $result);
        $this->assertSame($this->sse, $result);
    }

    public function test_can_set_event_id(): void
    {
        $result = $this->sse->setEventId('test-id');

        $this->assertInstanceOf(\StandaloneSSE::class, $result);
        $this->assertSame($this->sse, $result);
    }

    public function test_can_set_custom_header(): void
    {
        $result = $this->sse->setHeader('X-Custom', 'value');

        $this->assertInstanceOf(\StandaloneSSE::class, $result);
        $this->assertSame($this->sse, $result);
    }

    public function test_can_set_execution_time(): void
    {
        $result = $this->sse->setExecutionTime(300);

        $this->assertInstanceOf(\StandaloneSSE::class, $result);
        $this->assertSame($this->sse, $result);
    }

    public function test_is_connected_returns_boolean(): void
    {
        $this->assertIsBool($this->sse->isConnected());
    }

    public function test_event_method_returns_self(): void
    {
        ob_start();
        $result = $this->sse->event('test data', 'test-event', 'test-id');
        ob_end_clean();

        $this->assertInstanceOf(\StandaloneSSE::class, $result);
        $this->assertSame($this->sse, $result);
    }

    public function test_message_method_returns_self(): void
    {
        ob_start();
        $result = $this->sse->message('test message', 'msg-1');
        ob_end_clean();

        $this->assertInstanceOf(\StandaloneSSE::class, $result);
        $this->assertSame($this->sse, $result);
    }

    public function test_json_method_returns_self(): void
    {
        ob_start();
        $result = $this->sse->json(['key' => 'value'], 'json-event', 'json-1');
        ob_end_clean();

        $this->assertInstanceOf(\StandaloneSSE::class, $result);
        $this->assertSame($this->sse, $result);
    }

    public function test_comment_method_returns_self(): void
    {
        ob_start();
        $result = $this->sse->comment('Keep-alive');
        ob_end_clean();

        $this->assertInstanceOf(\StandaloneSSE::class, $result);
        $this->assertSame($this->sse, $result);
    }

    public function test_event_output_format(): void
    {
        ob_start();
        $this->sse->event('Hello World', 'greeting', 'msg-1');
        $output = ob_get_clean();

        $this->assertStringContainsString('id: msg-1', $output);
        $this->assertStringContainsString('event: greeting', $output);
        $this->assertStringContainsString('data: Hello World', $output);
    }

    public function test_json_output_format(): void
    {
        ob_start();
        $this->sse->json(['name' => 'John', 'age' => 30], 'user-data');
        $output = ob_get_clean();

        $this->assertStringContainsString('event: user-data', $output);
        $this->assertStringContainsString('data: {"name":"John","age":30}', $output);
    }

    public function test_comment_output_format(): void
    {
        ob_start();
        $this->sse->comment('This is a comment');
        $output = ob_get_clean();

        $this->assertStringContainsString(': This is a comment', $output);
    }

    public function test_message_output_format(): void
    {
        ob_start();
        $this->sse->message('Simple message');
        $output = ob_get_clean();

        $this->assertStringContainsString('data: Simple message', $output);
        $this->assertStringNotContainsString('event:', $output);
    }

    public function test_multiline_data_formatting(): void
    {
        ob_start();
        $this->sse->event("Line 1\nLine 2\nLine 3");
        $output = ob_get_clean();

        $this->assertStringContainsString('data: Line 1', $output);
        $this->assertStringContainsString('data: Line 2', $output);
        $this->assertStringContainsString('data: Line 3', $output);
    }

    public function test_method_chaining(): void
    {
        ob_start();
        $result = $this->sse
            ->setRetry(5000)
            ->setEventId('chain-test')
            ->setExecutionTime(300);
        ob_end_clean();

        $this->assertInstanceOf(\StandaloneSSE::class, $result);
        $this->assertSame($this->sse, $result);
    }
}
