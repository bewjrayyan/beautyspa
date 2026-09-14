<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('treatment_bookings')
            || Schema::hasColumn('treatment_bookings', 'tba_reminder_sent_at')
        ) {
            return;
        }

        Schema::table('treatment_bookings', function (Blueprint $table) {
            $table->timestamp('tba_reminder_sent_at')->nullable()->after('reminder_sent_at');
            $table->index('tba_reminder_sent_at', 'treatment_bookings_tba_reminder_index');
        });
    }


    public function down(): void
    {
        if (
            ! Schema::hasTable('treatment_bookings')
            || ! Schema::hasColumn('treatment_bookings', 'tba_reminder_sent_at')
        ) {
            return;
        }

        Schema::table('treatment_bookings', function (Blueprint $table) {
            $table->dropIndex('treatment_bookings_tba_reminder_index');
            $table->dropColumn('tba_reminder_sent_at');
        });
    }
};
