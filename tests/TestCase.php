<?php

namespace LaravelSSE\Tests;

use LaravelSSE\SSEServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            SSEServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'SSE' => \LaravelSSE\Facades\SSE::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default configuration
        $app['config']->set('sse.retry', 3000);
        $app['config']->set('sse.execution_time', 0);
    }
}
