<?php

namespace Modules\Report\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SyncReportTranslationsCommand extends Command
{
    protected $signature = 'report:sync-translations';

    protected $description = 'Sync Report module lang files to the database and refresh translation cache';

    public function handle(): int
    {
        $this->call('translation:refresh-cache', [
            '--sync' => true,
            '--module' => 'report',
        ]);

        if ($this->getApplication()->has('order:sync-translations')) {
            $this->call('order:sync-translations');
        } else {
            $this->call('translation:refresh-cache', [
                '--sync' => true,
                '--module' => 'order',
            ]);
        }

        $this->call('view:clear');

        $this->info('Report and order translations synced. Hard-refresh the admin page if labels still look stale.');

        return self::SUCCESS;
    }
}
