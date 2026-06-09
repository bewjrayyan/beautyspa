<?php

namespace Modules\Currency\Http\Controllers;

use Illuminate\Http\Response;

class CurrentCurrencyController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param string $currency
     *
     * @return Response
     */
    public function store($currency)
    {
        if (! in_array($currency, supported_currencies(), true)) {
            return back();
        }

        $cookie = cookie()->forever('currency', $currency);

        return back()->withCookie($cookie);
    }
}
