<?php

namespace Modules\Support\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;

class FaviconController
{
    public function redirect(): Response
    {
        $url = storefront_favicon_url();

        if (! $url) {
            abort(404);
        }

        return redirect()->away($url, 301);
    }
}
