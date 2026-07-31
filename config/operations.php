<?php

return [
    'privacy' => [
        // Execution stays disabled until the retention periods have legal approval.
        'retention_enabled' => env('PRIVACY_RETENTION_ENABLED', false),
        'completed_consultation_days' => env('PRIVACY_COMPLETED_CONSULTATION_DAYS'),
        'expired_request_days' => env('PRIVACY_EXPIRED_REQUEST_DAYS', 90),
        'consultation_access_log_days' => env('PRIVACY_CONSULTATION_ACCESS_LOG_DAYS', 730),
        'chunk_size' => env('PRIVACY_RETENTION_CHUNK_SIZE', 100),
    ],

    'queue' => [
        'monitor_enabled' => env('QUEUE_HEALTH_MONITOR_ENABLED', false),
        'max_pending' => env('QUEUE_HEALTH_MAX_PENDING', 100),
        'max_failed' => env('QUEUE_HEALTH_MAX_FAILED', 0),
        'max_oldest_pending_minutes' => env('QUEUE_HEALTH_MAX_PENDING_MINUTES', 10),
    ],

    'database' => [
        'slow_query_log_enabled' => env('SLOW_QUERY_LOG_ENABLED', false),
        'slow_query_ms' => env('SLOW_QUERY_LOG_MS', 500),
    ],
];
