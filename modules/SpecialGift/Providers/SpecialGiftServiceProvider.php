<?php

namespace Modules\SpecialGift\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SpecialGiftServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! config('app.installed')) {
            return;
        }

        Route::middleware('web')
            ->namespace('Modules\SpecialGift\Http\Controllers')
            ->group(module_path('SpecialGift', 'Routes/send_gift.php'));
    }
}
