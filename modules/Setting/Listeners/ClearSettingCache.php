<?php

namespace Modules\Setting\Listeners;

use Illuminate\Support\Facades\Cache;
use Modules\Page\Listeners\ClearPageResponseCache;

class ClearSettingCache
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle()
    {
        foreach (supported_locale_keys() as $locale) {
            Cache::forget(md5('settings.all:' . $locale));
        }

        if (app()->bound('setting')) {
            app()->forgetInstance('setting');
        }

        ClearPageResponseCache::flush();
    }
}
