<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class LocaleController extends Controller
{
    public function switch(string $locale): RedirectResponse
    {
        if (! in_array($locale, supported_locale_keys(), true)) {
            abort(404);
        }

        session(['locale' => $locale]);
        LaravelLocalization::setLocale($locale);

        return redirect()->back();
    }
}
