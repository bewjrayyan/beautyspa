<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('treatment_bookings') || Schema::hasColumn('treatment_bookings', 'customer_id')) {
            return;
        }

        Schema::table('treatment_bookings', function (Blueprint $table) {
            $table->unsignedInteger('customer_id')->nullable()->after('created_by_user_id');
            $table->index('customer_id', 'treatment_bookings_customer_id_index');
            $table->foreign('customer_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('treatment_bookings') || ! Schema::hasColumn('treatment_bookings', 'customer_id')) {
            return;
        }

        Schema::table('treatment_bookings', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropIndex('treatment_bookings_customer_id_index');
            $table->dropColumn('customer_id');
        });
    }
};
