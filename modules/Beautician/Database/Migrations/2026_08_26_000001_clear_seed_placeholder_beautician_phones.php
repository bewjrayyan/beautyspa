<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clear seeder placeholder phones so OneSender never targets fake numbers.
     * Set real phones from Admin → Beauticians; admin notify list from Settings → WhatsApp.
     */
    public function up(): void
    {
        if (! Schema::hasTable('beauticians') || ! Schema::hasColumn('beauticians', 'phone')) {
            return;
        }

        DB::table('beauticians')
            ->whereRaw('phone REGEXP ?', ['^[+]?6010{7,}[0-9]?$'])
            ->update(['phone' => null]);
    }


    public function down(): void
    {
        // Intentionally empty — do not restore placeholder phones.
    }
};
