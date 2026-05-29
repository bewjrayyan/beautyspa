<?php

namespace Modules\TreatmentReservation\Console;

use Illuminate\Console\Command;
use Modules\TreatmentReservation\Services\CustomerAppointmentReminderService;

class SendCustomerAppointmentRemindersCommand extends Command
{
    protected $signature = 'treatment-reservations:send-customer-appointment-reminders';

    protected $description = 'Send WhatsApp reminders to customers before upcoming treatment appointments';


    public function handle(CustomerAppointmentReminderService $service): int
    {
        $count = $service->sendDueReminders();

        $this->info("Sent {$count} customer appointment reminder(s).");

        return self::SUCCESS;
    }
}
