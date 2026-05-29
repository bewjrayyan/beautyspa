<?php

namespace Modules\Page\Listeners;

use Illuminate\Support\Facades\Cache;

class ClearPageResponseCache
{
    public static function flush(): void
    {
        if (Cache::has('page_response_version')) {
            Cache::increment('page_response_version');

            return;
        }

        Cache::forever('page_response_version', 2);
    }
}
