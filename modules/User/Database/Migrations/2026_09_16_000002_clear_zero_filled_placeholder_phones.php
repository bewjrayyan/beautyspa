<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'beauticians'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'phone')) {
                continue;
            }

            // Legacy users.phone is non-nullable; an empty value is the existing
            // no-phone representation and is never a WhatsApp recipient.
            $replacement = $table === 'users' ? '' : null;

            DB::table($table)
                ->whereRaw('phone REGEXP ?', ['^[+]?6010{7,}[0-9]?$'])
                ->update(['phone' => $replacement]);
        }
    }


    public function down(): void
    {
        // Placeholder phones are intentionally never restored.
    }
};
