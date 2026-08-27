<?php

namespace Modules\WhatsappBirthdayReminder\Console;

use Illuminate\Console\Command;
use Modules\WhatsappBirthdayReminder\Services\BirthdayCustomerFinder;
use Modules\WhatsappBirthdayReminder\Services\BirthdayReminderConfig;
use Modules\WhatsappBirthdayReminder\Services\BirthdayReminderService;

class SendBirthdayRemindersCommand extends Command
{
    protected $signature = 'whatsapp-birthday:send
                            {--force : Process even when the module is disabled}
                            {--dry-run : List birthday customers without sending}';

    protected $description = 'Send WhatsApp birthday greetings with configured loyalty rewards.';


    public function handle(
        BirthdayReminderService $service,
        BirthdayReminderConfig $config,
    ): int {
        if (! $config->enabled() && ! $this->option('force')) {
            $this->warn('WhatsApp Birthday Reminder is disabled. Use --force to run anyway.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $customers = app(BirthdayCustomerFinder::class)->forToday();
            $this->info('Birthday customers today: '.$customers->count());

            foreach ($customers as $user) {
                $this->line(" - #{$user->id} {$user->first_name} ({$user->phone})");
            }

            return self::SUCCESS;
        }

        $stats = $service->processToday((bool) $this->option('force'));

        $this->info(trans('whatsappbirthday::messages.command_summary', $stats));

        return self::SUCCESS;
    }
}
