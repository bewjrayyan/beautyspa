<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HTTP security headers
    |--------------------------------------------------------------------------
    |
    | CSP is enforced by default. Use SECURITY_CSP_REPORT_ONLY=true temporarily
    | while debugging third-party scripts, then set it back to false.
    |
    */
    'headers' => [
        'enabled' => env('SECURITY_HEADERS_ENABLED', true),

        'csp_enabled' => env('SECURITY_CSP_ENABLED', true),

        'csp_report_only' => env('SECURITY_CSP_REPORT_ONLY', false),

        'csp_report_uri' => env('SECURITY_CSP_REPORT_URI'),

        'hsts_enabled' => env('SECURITY_HSTS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom header/footer assets (storefront)
    |--------------------------------------------------------------------------
    |
    | Inline <script> is blocked unless allow_inline_scripts=true.
    | External scripts must come from the HTTPS host allowlist below.
    |
    */
    'custom_assets' => [
        'allow_inline_scripts' => env('SECURITY_ALLOW_INLINE_CUSTOM_SCRIPTS', false),

        'script_host_allowlist' => array_values(array_filter(array_map('trim', explode(',', (string) env(
            'SECURITY_CUSTOM_SCRIPT_HOSTS',
            implode(',', [
                'www.googletagmanager.com',
                'www.google-analytics.com',
                'www.google.com',
                'www.gstatic.com',
                'googleads.g.doubleclick.net',
                'connect.facebook.net',
                'www.facebook.com',
                'static.cloudflareinsights.com',
                'cdn.jsdelivr.net',
                'cdnjs.cloudflare.com',
                'unpkg.com',
                'www.clarity.ms',
                'scripts.clarity.ms',
                'static.hotjar.com',
                'script.hotjar.com',
                'www.paypal.com',
                'www.paypalobjects.com',
                'js.stripe.com',
                'www.recaptcha.net',
            ])
        ))))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Trusted reverse proxies
    |--------------------------------------------------------------------------
    */
    'trusted_proxies' => env('TRUSTED_PROXIES'),
];
