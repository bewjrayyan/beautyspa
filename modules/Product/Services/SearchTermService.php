<?php

namespace Modules\Product\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Product\Entities\SearchTerm;

class SearchTermService
{
    public const CACHE_KEY = 'most_searched_keywords';

    public function reset(): void
    {
        SearchTerm::query()->delete();

        Cache::forget(self::CACHE_KEY);
    }
}
