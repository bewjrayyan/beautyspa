<?php

namespace Modules\TreatmentReservation\Console;

use Illuminate\Console\Command;
use Modules\TreatmentReservation\Services\CustomerFollowUpNotificationService;

class SendCustomerFollowUpNotificationsCommand extends Command
{
    protected $signature = 'treatment-reservations:send-customer-followups';

    protected $description = 'Send WhatsApp follow-up messages to customers after completed treatments';


    public function handle(CustomerFollowUpNotificationService $service): int
    {
        $count = $service->sendDueFollowUps();

        $this->info("Sent {$count} customer follow-up message(s).");

        return self::SUCCESS;
    }
}
