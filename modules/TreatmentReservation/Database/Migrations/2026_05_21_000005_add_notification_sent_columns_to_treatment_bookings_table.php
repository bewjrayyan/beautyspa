<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_bookings', function (Blueprint $table) {
            $table->timestamp('completed_notification_sent_at')->nullable()->after('customer_reminder_sent_at');
            $table->timestamp('followup_sent_at')->nullable()->after('completed_notification_sent_at');
        });
    }


    public function down(): void
    {
        Schema::table('treatment_bookings', function (Blueprint $table) {
            $table->dropColumn(['completed_notification_sent_at', 'followup_sent_at']);
        });
    }
};
