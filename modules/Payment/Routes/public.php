<?php

use Illuminate\Support\Facades\Route;
use AestheticCart\Http\Middleware\VerifyCsrfToken;
use Modules\Payment\Http\Controllers\ChipWebhookController;

Route::post('payment/chip/webhook', [ChipWebhookController::class, 'handle'])
    ->name('payment.chip.webhook')
    ->withoutMiddleware(VerifyCsrfToken::class);
