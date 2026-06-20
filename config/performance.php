<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CDN URLs
    |--------------------------------------------------------------------------
    |
    | Optional CDN origins (no trailing slash). When set, media/asset URLs
    | pointing at APP_URL are rewritten for faster global delivery.
    |
    | Example: MEDIA_CDN_URL=https://cdn.immaserilaris.com
    |
    */
    'cdn' => [
        'asset_url' => env('ASSET_CDN_URL'),
        'media_url' => env('MEDIA_CDN_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storefront layout cache TTL (seconds)
    |--------------------------------------------------------------------------
    */
    'layout_cache_ttl' => (int) env('STOREFRONT_LAYOUT_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Image optimization (upload)
    |--------------------------------------------------------------------------
    */
    'image' => [
        'webp_enabled' => env('IMAGE_WEBP_ENABLED', true),
        'replace_original_with_webp' => env('IMAGE_REPLACE_WITH_WEBP', true),
        'webp_quality' => (int) env('IMAGE_WEBP_QUALITY', 82),
        'widths' => array_map('intval', array_filter(explode(',', env('IMAGE_RESPONSIVE_WIDTHS', '480,960')))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Full-page HTML cache (guest CMS pages)
    |--------------------------------------------------------------------------
    */
    'response_cache' => [
        'enabled' => env('RESPONSE_CACHE_ENABLED', false),
        'home_enabled' => env('RESPONSE_CACHE_HOME_ENABLED', false),
        'ttl_minutes' => (int) env('RESPONSE_CACHE_TTL_MINUTES', 60),
        'slugs' => array_filter(array_map('trim', explode(',', env('RESPONSE_CACHE_SLUGS', 'faq,terms-conditions,privacy-policy')))),
    ],
];
