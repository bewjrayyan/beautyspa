<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('google_sheets_synced_at')->nullable()->after('appointment_time');
            $table->string('google_calendar_event_id')->nullable()->after('google_sheets_synced_at');
        });
    }


    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['google_sheets_synced_at', 'google_calendar_event_id']);
        });
    }
};
