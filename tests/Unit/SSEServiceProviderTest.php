<?php

namespace LaravelSSE\Tests\Unit;

use LaravelSSE\SSE;
use LaravelSSE\SSEServiceProvider;
use LaravelSSE\Tests\TestCase;

class SSEServiceProviderTest extends TestCase
{
    public function test_service_provider_is_registered(): void
    {
        $this->assertTrue($this->app->providerIsLoaded(SSEServiceProvider::class));
    }

    public function test_sse_is_bound_to_container(): void
    {
        $this->assertTrue($this->app->bound('sse'));
        $this->assertTrue($this->app->bound(SSE::class));
    }

    public function test_sse_is_singleton(): void
    {
        $instance1 = $this->app->make('sse');
        $instance2 = $this->app->make('sse');

        $this->assertSame($instance1, $instance2);
    }

    public function test_sse_resolves_to_correct_class(): void
    {
        $sse = $this->app->make('sse');

        $this->assertInstanceOf(SSE::class, $sse);
    }

    public function test_sse_alias_resolves_correctly(): void
    {
        $sseFromAlias = $this->app->make('sse');
        $sseFromClass = $this->app->make(SSE::class);

        $this->assertSame($sseFromAlias, $sseFromClass);
    }

    public function test_config_is_merged(): void
    {
        $this->assertNotNull(config('sse.retry'));
        $this->assertNotNull(config('sse.execution_time'));
    }

    public function test_config_has_default_values(): void
    {
        $this->assertEquals(3000, config('sse.retry'));
        $this->assertEquals(0, config('sse.execution_time'));
    }

    public function test_provides_method_returns_correct_services(): void
    {
        $provider = new SSEServiceProvider($this->app);
        $provided = $provider->provides();

        $this->assertContains('sse', $provided);
        $this->assertContains(SSE::class, $provided);
    }

    public function test_sse_can_be_resolved_via_dependency_injection(): void
    {
        $resolved = $this->app->make(SSE::class);

        $this->assertInstanceOf(SSE::class, $resolved);
        $this->assertSame($this->app->make('sse'), $resolved);
    }
}
