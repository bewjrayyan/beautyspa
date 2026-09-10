<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Modules\Setting\Entities\Setting;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            Setting::set('whatsapp_customer_reminder_minutes', 1440);
        }
    }


    public function down(): void
    {
        // Preserve the administrator's current reminder preference on rollback.
    }
};
