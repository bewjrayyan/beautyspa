<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HTTP security headers
    |--------------------------------------------------------------------------
    |
    | CSP defaults to report-only so you can tune policy without breaking checkout.
    | Set SECURITY_CSP_ENABLED=true and SECURITY_CSP_REPORT_ONLY=false when ready.
    |
    */
    'headers' => [
        'enabled' => env('SECURITY_HEADERS_ENABLED', true),

        'csp_enabled' => env('SECURITY_CSP_ENABLED', false),

        'csp_report_only' => env('SECURITY_CSP_REPORT_ONLY', true),

        'csp_report_uri' => env('SECURITY_CSP_REPORT_URI'),

        'hsts_enabled' => env('SECURITY_HSTS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Trusted reverse proxies
    |--------------------------------------------------------------------------
    |
    | Comma-separated proxy IPs, or "*" to trust all (not recommended in prod).
    | Leave empty when the app is reached directly without a load balancer.
    |
    */
    'trusted_proxies' => env('TRUSTED_PROXIES'),
];
