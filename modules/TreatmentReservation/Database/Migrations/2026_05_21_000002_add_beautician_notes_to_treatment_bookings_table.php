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
            if (! Schema::hasColumn('treatment_bookings', 'beautician_notes')) {
                $table->text('beautician_notes')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('treatment_bookings', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable()->after('beautician_notes');
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
                Schema::hasColumn('treatment_bookings', 'beautician_notes') ? 'beautician_notes' : null,
                Schema::hasColumn('treatment_bookings', 'reminder_sent_at') ? 'reminder_sent_at' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
