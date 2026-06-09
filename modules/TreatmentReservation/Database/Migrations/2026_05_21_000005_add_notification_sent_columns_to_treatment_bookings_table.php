<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('treatment_bookings')) {
            return;
        }

        Schema::table('treatment_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('treatment_bookings', 'completed_notification_sent_at')) {
                $table->timestamp('completed_notification_sent_at')->nullable()->after('customer_reminder_sent_at');
            }

            if (! Schema::hasColumn('treatment_bookings', 'followup_sent_at')) {
                $table->timestamp('followup_sent_at')->nullable()->after('completed_notification_sent_at');
            }
        });
    }


    public function down(): void
    {
        if (! Schema::hasTable('treatment_bookings')) {
            return;
        }

        Schema::table('treatment_bookings', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('treatment_bookings', 'completed_notification_sent_at') ? 'completed_notification_sent_at' : null,
                Schema::hasColumn('treatment_bookings', 'followup_sent_at') ? 'followup_sent_at' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
