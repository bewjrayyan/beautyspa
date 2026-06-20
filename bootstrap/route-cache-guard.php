<?php

/**
 * `php artisan route:cache` / `optimize` boot a fresh app before module storefront
 * routes are registered, so the cached file omits `home` and other public routes.
 * Remove incomplete caches so Laravel falls back to live route registration.
 */
(function (): void {
    $path = __DIR__ . '/cache/routes-v7.php';

    if (! is_file($path)) {
        return;
    }

    $contents = @file_get_contents($path);

    if ($contents === false || str_contains($contents, 'HomeController')) {
        return;
    }

    @unlink($path);
})();
