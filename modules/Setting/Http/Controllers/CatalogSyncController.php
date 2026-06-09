<?php

namespace Modules\Setting\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Setting\Services\CatalogSyncService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CatalogSyncController
{
    public function __construct(
        private CatalogSyncService $catalogSync
    ) {
    }

    public function bundle(Request $request): BinaryFileResponse
    {
        if (! $this->catalogSync->isTokenValid($request->query('token'))) {
            abort(403, 'Invalid catalog sync token.');
        }

        $bundle = $this->catalogSync->createBundle();

        return response()->download(
            $bundle['path'],
            config('setting.catalog_sync.bundle_filename', 'catalog-bundle.zip'),
            ['Content-Type' => 'application/zip']
        )->deleteFileAfterSend(false);
    }
}
