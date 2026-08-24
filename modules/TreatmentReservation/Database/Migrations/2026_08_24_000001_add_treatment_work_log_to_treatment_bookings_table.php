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
            if (! Schema::hasColumn('treatment_bookings', 'beautician_notes_at')) {
                $table->dateTime('beautician_notes_at')->nullable()->after('beautician_notes');
            }

            if (! Schema::hasColumn('treatment_bookings', 'beautician_checklist')) {
                $table->json('beautician_checklist')->nullable()->after('beautician_notes_at');
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
                Schema::hasColumn('treatment_bookings', 'beautician_checklist') ? 'beautician_checklist' : null,
                Schema::hasColumn('treatment_bookings', 'beautician_notes_at') ? 'beautician_notes_at' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
