<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('appointment_availability_locks')) {
            Schema::create('appointment_availability_locks', function (Blueprint $table) {
                $table->string('lock_key', 191)->primary();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('treatment_bookings') && ! Schema::hasColumn('treatment_bookings', 'duration_minutes_snapshot')) {
            Schema::table('treatment_bookings', function (Blueprint $table) {
                $table->unsignedSmallInteger('duration_minutes_snapshot')->nullable()->after('appointment_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('treatment_bookings') && Schema::hasColumn('treatment_bookings', 'duration_minutes_snapshot')) {
            Schema::table('treatment_bookings', function (Blueprint $table) {
                $table->dropColumn('duration_minutes_snapshot');
            });
        }

        Schema::dropIfExists('appointment_availability_locks');
    }
};
