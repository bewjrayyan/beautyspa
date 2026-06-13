<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('treatment_bookings', 'source')) {
                $table->string('source', 32)->default('checkout')->after('order_id');
            }

            if (! Schema::hasColumn('treatment_bookings', 'created_by_user_id')) {
                $table->unsignedInteger('created_by_user_id')->nullable()->after('source');
                $table->index('created_by_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('treatment_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('treatment_bookings', 'created_by_user_id')) {
                $table->dropIndex(['created_by_user_id']);
                $table->dropColumn('created_by_user_id');
            }

            if (Schema::hasColumn('treatment_bookings', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
