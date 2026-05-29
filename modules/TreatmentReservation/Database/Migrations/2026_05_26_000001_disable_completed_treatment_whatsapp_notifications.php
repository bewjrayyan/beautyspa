<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Setting\Entities\Setting;

return new class extends Migration
{
    public function up(): void
    {
        Setting::set('whatsapp_customer_completed_enabled', false);

        if (Schema::hasColumn('treatment_bookings', 'completed_notification_sent_at')) {
            DB::table('treatment_bookings')
                ->whereNull('completed_notification_sent_at')
                ->update(['completed_notification_sent_at' => now()]);
        }
    }


    public function down(): void
    {
        //
    }
};
