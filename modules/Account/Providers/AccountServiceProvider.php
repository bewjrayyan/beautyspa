<?php

namespace Modules\Account\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Account\Contracts\PdfRenderer;
use Modules\Account\Services\DompdfPdfRenderer;

class AccountServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PdfRenderer::class, DompdfPdfRenderer::class);
    }
}
