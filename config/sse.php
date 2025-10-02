<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Retry Time
    |--------------------------------------------------------------------------
    |
    | The retry time in milliseconds that the browser should wait before
    | attempting to reconnect if the connection is lost.
    |
    */
    'retry' => env('SSE_RETRY', 3000),

    /*
    |--------------------------------------------------------------------------
    | Execution Time
    |--------------------------------------------------------------------------
    |
    | The maximum execution time in seconds for SSE streams.
    | Set to 0 for unlimited execution time.
    |
    */
    'execution_time' => env('SSE_EXECUTION_TIME', 0),
];
