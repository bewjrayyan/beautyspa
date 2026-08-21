<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schedule state for appointments purchased without a fixed slot.
     * Value is only "tba" (or null once a real date/time is assigned).
     * Kept separate from treatment_bookings.status (pending/in_progress/completed/canceled).
     */
    public function up(): void
    {
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'schedule_status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('schedule_status', 10)->nullable()->after('appointment_time');
                $table->index(['schedule_status', 'beautician_id'], 'orders_schedule_status_beautician_index');
            });
        }

        if (Schema::hasTable('treatment_bookings') && ! Schema::hasColumn('treatment_bookings', 'schedule_status')) {
            Schema::table('treatment_bookings', function (Blueprint $table) {
                $table->string('schedule_status', 10)->nullable()->after('appointment_time');
                $table->index(['schedule_status', 'beautician_id', 'status'], 'treatment_bookings_schedule_status_index');
            });
        }

        // Existing unscheduled treatment orders/bookings → TBA (compatible backfill).
        if (Schema::hasColumn('orders', 'schedule_status')) {
            DB::table('orders')
                ->whereNotNull('beautician_id')
                ->whereNull('appointment_time')
                ->whereNull('schedule_status')
                ->update(['schedule_status' => 'tba']);
        }

        if (Schema::hasColumn('treatment_bookings', 'schedule_status')) {
            DB::table('treatment_bookings')
                ->whereNotNull('beautician_id')
                ->whereNull('appointment_time')
                ->whereNull('schedule_status')
                ->whereIn('status', ['pending', 'in_progress'])
                ->update(['schedule_status' => 'tba']);
        }
    }


    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'schedule_status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_schedule_status_beautician_index');
                $table->dropColumn('schedule_status');
            });
        }

        if (Schema::hasTable('treatment_bookings') && Schema::hasColumn('treatment_bookings', 'schedule_status')) {
            Schema::table('treatment_bookings', function (Blueprint $table) {
                $table->dropIndex('treatment_bookings_schedule_status_index');
                $table->dropColumn('schedule_status');
            });
        }
    }
};
