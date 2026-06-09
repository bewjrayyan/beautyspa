<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('treatment_bookings')
            || Schema::hasColumn('treatment_bookings', 'deleted_at')) {
            return;
        }

        Schema::table('treatment_bookings', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('treatment_bookings')
            || ! Schema::hasColumn('treatment_bookings', 'deleted_at')) {
            return;
        }

        Schema::table('treatment_bookings', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
