<?php

namespace LaravelSSE;

use Illuminate\Support\ServiceProvider;

class SSEServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/sse.php', 'sse'
        );

        $this->app->singleton('sse', function ($app) {
            $sse = new SSE();

            if ($retry = config('sse.retry')) {
                $sse->setRetry($retry);
            }

            if ($executionTime = config('sse.execution_time')) {
                $sse->setExecutionTime($executionTime);
            }

            return $sse;
        });

        $this->app->alias('sse', SSE::class);
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/sse.php' => config_path('sse.php'),
            ], 'sse-config');
        }
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return ['sse', SSE::class];
    }
}
