<?php

use Illuminate\Support\Facades\Route;

/*
| Locale-free signed document URLs: /secure/... (not /{locale}/secure/...).
| Uses signed.subdirectory:relative so signatures match after FixSubdirectoryRequest strips the install base.
*/

$locales = implode('|', array_map(
    static fn (string $locale) => preg_quote($locale, '/'),
    function_exists('supported_locale_keys') ? supported_locale_keys() : ['en', 'ms']
));

if ($locales !== '') {
    // Restore signature-valid path when an older localized URL is opened.
    Route::get('{locale}/secure/{path}', function (string $locale, string $path) {
        $target = url('secure/'.$path);
        $query = request()->getQueryString();

        if (is_string($query) && $query !== '') {
            $target .= '?'.$query;
        }

        return redirect()->to($target, 301);
    })->where([
        'locale' => $locales,
        'path' => '.*',
    ]);
}

Route::get('secure/order/{order}/payment-proof/{file}', 'OrderPaymentProofController@show')
    ->middleware(['signed.subdirectory:relative', 'throttle:30,1'])
    ->name('order.payment_proofs.temporary');

Route::get('secure/order/{order}/document/{type}/{fingerprint}', 'OrderTemporaryDocumentController@show')
    ->middleware(['signed.subdirectory:relative', 'throttle:30,1'])
    ->name('order.documents.temporary');
