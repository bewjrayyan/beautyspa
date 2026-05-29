<?php

namespace Modules\TreatmentReservation\Console;

use Illuminate\Console\Command;
use Modules\TreatmentReservation\Services\BeauticianAppointmentReminderService;

class SendBeauticianAppointmentRemindersCommand extends Command
{
    protected $signature = 'treatment-reservations:send-appointment-reminders';

    protected $description = 'Send WhatsApp reminders to beauticians before upcoming appointments';


    public function handle(BeauticianAppointmentReminderService $service): int
    {
        $count = $service->sendDueReminders();

        $this->info("Sent {$count} beautician appointment reminder(s).");

        return self::SUCCESS;
    }
}
