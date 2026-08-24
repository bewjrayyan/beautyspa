<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('treatment_bookings') && ! Schema::hasColumn('treatment_bookings', 'google_calendar_event_id')) {
            Schema::table('treatment_bookings', function (Blueprint $table): void {
                $table->string('google_calendar_event_id')->nullable();
            });
        }
    }


    public function down(): void
    {
        if (Schema::hasTable('treatment_bookings') && Schema::hasColumn('treatment_bookings', 'google_calendar_event_id')) {
            Schema::table('treatment_bookings', function (Blueprint $table): void {
                $table->dropColumn('google_calendar_event_id');
            });
        }
    }
};
