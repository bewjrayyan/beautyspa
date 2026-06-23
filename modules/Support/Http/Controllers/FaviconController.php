<?php

namespace Modules\Support\Http\Controllers;

use Modules\Support\Services\FaviconService;
use Symfony\Component\HttpFoundation\Response;

class FaviconController
{
    public function show(FaviconService $faviconService): Response
    {
        $file = storefront_favicon_file();

        if (! $file) {
            abort(404);
        }

        $ico = $faviconService->getIco($file);

        if ($ico === null) {
            abort(404);
        }

        return response($ico, 200, [
            'Content-Type' => 'image/x-icon',
            'Cache-Control' => 'public, max-age=604800, immutable',
        ]);
    }
}
