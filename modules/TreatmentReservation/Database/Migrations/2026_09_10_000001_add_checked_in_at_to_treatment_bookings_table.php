<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('treatment_bookings') || Schema::hasColumn('treatment_bookings', 'checked_in_at')) {
            return;
        }

        Schema::table('treatment_bookings', function (Blueprint $table): void {
            $table->dateTime('checked_in_at')->nullable()->after('appointment_time');
            $table->index(['appointment_date', 'checked_in_at'], 'tr_bookings_appointment_checkin_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('treatment_bookings') || ! Schema::hasColumn('treatment_bookings', 'checked_in_at')) {
            return;
        }

        Schema::table('treatment_bookings', function (Blueprint $table): void {
            $table->dropIndex('tr_bookings_appointment_checkin_idx');
            $table->dropColumn('checked_in_at');
        });
    }
};
