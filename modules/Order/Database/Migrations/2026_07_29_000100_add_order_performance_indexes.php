<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->index(
                ['beautician_id', 'appointment_date', 'status', 'appointment_time'],
                'orders_beautician_date_status_time_idx'
            );
            $table->index(
                ['payment_status', 'created_at'],
                'orders_payment_status_created_idx'
            );
            $table->index(
                ['status', 'created_at'],
                'orders_status_created_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_beautician_date_status_time_idx');
            $table->dropIndex('orders_payment_status_created_idx');
            $table->dropIndex('orders_status_created_idx');
        });
    }
};
