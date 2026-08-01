<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The admin dashboard lists today's and upcoming appointments across every
     * beautician, so it cannot use orders_beautician_date_status_time_idx,
     * which leads with beautician_id. Without an appointment_date-leading
     * index those widgets fall back to scanning the whole orders table.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->index(['appointment_date', 'status'], 'orders_appointment_date_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_appointment_date_status_idx');
        });
    }
};
