<?php

namespace Modules\GoogleIntegration\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Order\Entities\Order;

class GoogleSheetsSyncFailed extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;


    public function __construct(
        public readonly Order $order,
        public readonly string $errorMessage,
        public readonly string $trigger,
    ) {
    }


    public function build(): self
    {
        return $this->subject(trans('setting::messages.google_sheets_alert_email_subject', [
            'order' => $this->order->id,
        ]))->view('googleintegration::emails.google_sheets_sync_failed', [
            'order' => $this->order,
            'errorMessage' => $this->errorMessage,
            'trigger' => $this->trigger,
        ]);
    }
}
