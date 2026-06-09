<?php

namespace Modules\Support\Cache;

use Illuminate\Support\Facades\Cache;

class TaggedCache
{
    /**
     * @param  string|array<int, string>  $tags
     */
    public static function rememberForever(string|array $tags, string $key, callable $callback): mixed
    {
        if (! config('app.cache')) {
            return $callback();
        }

        try {
            return Cache::tags($tags)->rememberForever($key, $callback);
        } catch (\Throwable) {
            return $callback();
        }
    }


    public static function remember(string|array $tags, string $key, \DateTimeInterface|\DateInterval|int|null $ttl, callable $callback): mixed
    {
        if (! config('app.cache')) {
            return $callback();
        }

        try {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        } catch (\Throwable) {
            return $callback();
        }
    }


    public static function rememberPlainForever(string $key, callable $callback): mixed
    {
        if (! config('app.cache')) {
            return $callback();
        }

        try {
            return Cache::rememberForever($key, $callback);
        } catch (\Throwable) {
            return $callback();
        }
    }
}
