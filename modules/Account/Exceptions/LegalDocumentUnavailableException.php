<?php

namespace Modules\Account\Exceptions;

use Illuminate\Http\Response;
use RuntimeException;

class LegalDocumentUnavailableException extends RuntimeException
{
    public function render(): Response
    {
        return response()->view('errors.503', status: 503);
    }
}
