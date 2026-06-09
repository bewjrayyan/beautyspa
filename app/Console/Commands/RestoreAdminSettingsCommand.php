<?php

namespace AestheticCart\Console\Commands;

use AestheticCart\Install\ImmaSeriLarisAdminSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RestoreAdminSettingsCommand extends Command
{
    protected $signature = 'settings:restore-imma
                            {--force-contact : Overwrite store email, phone, and address even if already set}';

    protected $description = 'Restore Admin → Settings fields for Imma Seri Laris (store, mail, WhatsApp, loyalty)';

    public function handle(): int
    {
        $applied = (new ImmaSeriLarisAdminSettings())->apply(
            (bool) $this->option('force-contact')
        );

        Artisan::call('optimize:clear');

        $this->info('Admin settings restored (' . count($applied) . ' keys).');
        $this->newLine();
        $this->table(['Setting', 'Value'], [
            ['store_name', setting('store_name')],
            ['store_email', setting('store_email')],
            ['store_phone', setting('store_phone')],
            ['mail_from_address', setting('mail_from_address')],
            ['default_country', setting('default_country')],
            ['default_currency', setting('default_currency')],
        ]);

        if ($applied !== []) {
            $this->newLine();
            $this->line('Updated: ' . implode(', ', array_slice($applied, 0, 20))
                . (count($applied) > 20 ? '…' : ''));
        }

        $this->newLine();
        $this->info('Open: ' . url('/admin/settings'));

        return self::SUCCESS;
    }
}
