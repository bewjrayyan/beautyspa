<?php

namespace Modules\Setting\Console;

use Illuminate\Console\Command;
use Modules\Setting\Support\WhatsAppNotificationDefaults;

class RefreshWhatsAppTemplatesCommand extends Command
{
    protected $signature = 'setting:refresh-whatsapp-templates
                            {--order-only : Refresh order-related templates only}
                            {--force : Overwrite existing templates in the database}';

    protected $description = 'Refresh WhatsApp message templates from config defaults';

    public function handle(): int
    {
        $applied = WhatsAppNotificationDefaults::refreshMessageTemplates(
            orderOnly: (bool) $this->option('order-only'),
            force: (bool) $this->option('force'),
        );

        if ($applied === []) {
            $this->info('No WhatsApp templates were updated.');

            return self::SUCCESS;
        }

        $this->info('Updated '.count($applied).' WhatsApp template(s):');

        foreach ($applied as $key) {
            $this->line(' - '.$key);
        }

        return self::SUCCESS;
    }
}
