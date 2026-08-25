<?php

namespace Modules\Setting\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class OperationsHeartbeatCommand extends Command
{
    protected $signature = 'operations:heartbeat';

    protected $description = 'Record a scheduler heartbeat for the admin operations dashboard';

    public function handle(): int
    {
        try {
            if (! Schema::hasTable('operation_heartbeats')) {
                return self::SUCCESS;
            }

            DB::table('operation_heartbeats')->updateOrInsert(
                ['name' => 'scheduler'],
                ['last_seen_at' => now(), 'created_at' => now(), 'updated_at' => now()]
            );
        } catch (Throwable $e) {
            // Transient MySQL outages should not spam schedule failure noise every minute.
            $this->warn('operations:heartbeat skipped: '.$e->getMessage());
        }

        return self::SUCCESS;
    }
}
