<?php

declare(strict_types=1);

return [
    /*
     * Whether the redis extension is required.
     * If true, the check will fail if the extension is not installed.
     * If false, it will only result in a warning.
     */
    'required' => env('HEALTH_REDIS_REQUIRED', false),

    /*
     * Warn when Redis memory usage reaches this percentage of its maxmemory limit.
     * A usage of 100% or more is always reported as failed.
     */
    'memory_warning_threshold_percent' => env('HEALTH_REDIS_MEMORY_WARNING_PERCENT', 90),
];
