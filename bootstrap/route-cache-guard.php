<?php

/**
 * Route caching is disabled for AestheticCart (module storefront routes are not
 * registered during route:cache / route:trans:cache). Remove any stale cache files
 * so Laravel always loads live routes and `route('home')` stays available.
 */
(function (): void {
    $cacheDir = __DIR__ . '/cache';

    foreach (glob($cacheDir . '/routes-v7*.php') ?: [] as $path) {
        @unlink($path);
    }
})();
