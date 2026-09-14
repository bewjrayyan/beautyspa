<?php

namespace Modules\TreatmentReservation\Console;

use Illuminate\Console\Command;
use Modules\TreatmentReservation\Services\BeauticianTbaReminderService;

class SendBeauticianTbaRemindersCommand extends Command
{
    protected $signature = 'treatment-reservations:send-tba-reminders';

    protected $description = 'Remind beauticians about assigned TBA appointments that still need a date and time';


    public function handle(BeauticianTbaReminderService $service): int
    {
        $count = $service->sendDueReminders();

        $this->info("Sent {$count} beautician TBA reminder(s).");

        return self::SUCCESS;
    }
}
