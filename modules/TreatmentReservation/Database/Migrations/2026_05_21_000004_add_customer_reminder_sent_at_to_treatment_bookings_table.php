<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('treatment_bookings')
            || Schema::hasColumn('treatment_bookings', 'customer_reminder_sent_at')) {
            return;
        }

        Schema::table('treatment_bookings', function (Blueprint $table) {
            $table->timestamp('customer_reminder_sent_at')->nullable()->after('reminder_sent_at');
        });
    }


    public function down(): void
    {
        if (! Schema::hasTable('treatment_bookings')
            || ! Schema::hasColumn('treatment_bookings', 'customer_reminder_sent_at')) {
            return;
        }

        Schema::table('treatment_bookings', function (Blueprint $table) {
            $table->dropColumn('customer_reminder_sent_at');
        });
    }
};
