<?php

namespace Modules\Checkout\Listeners;

use Throwable;
use Illuminate\Support\Facades\Log;
use Modules\Checkout\Mail\Invoice;
use Modules\Checkout\Mail\NewOrder;
use Illuminate\Support\Facades\Mail;
use Modules\Checkout\Events\OrderPlaced;

class SendNewOrderEmails
{
    /**
     * Handle the event.
     *
     * @param OrderPlaced $event
     *
     * @return void
     */
    public function handle(OrderPlaced $event)
    {
        if (! $this->canSendMail()) {
            return;
        }

        try {
            if (setting('admin_order_email')) {
                Mail::to(setting('store_email'))
                    ->send(new NewOrder($event->order));
            }

            if (setting('invoice_email')) {
                Mail::to($event->order->customer_email)
                    ->send(new Invoice($event->order));
            }
        } catch (Throwable $e) {
            Log::warning('Order notification email failed', [
                'order_id' => $event->order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }


    private function canSendMail(): bool
    {
        if (config('mail.default') === 'log') {
            return true;
        }

        return filled(config('mail.mailers.smtp.host'));
    }
}
