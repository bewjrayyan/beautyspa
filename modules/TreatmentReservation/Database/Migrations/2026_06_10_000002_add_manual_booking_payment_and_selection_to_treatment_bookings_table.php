<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('treatment_bookings', 'variant_id')) {
                $table->unsignedInteger('variant_id')->nullable()->after('product_id');
            }

            if (! Schema::hasColumn('treatment_bookings', 'product_options')) {
                $table->json('product_options')->nullable()->after('variant_id');
            }

            if (! Schema::hasColumn('treatment_bookings', 'product_variations')) {
                $table->json('product_variations')->nullable()->after('product_options');
            }

            if (! Schema::hasColumn('treatment_bookings', 'payment_status')) {
                $table->string('payment_status', 20)->nullable()->default('deposit')->after('total');
            }

            if (! Schema::hasColumn('treatment_bookings', 'payment_receipt_file_id')) {
                $table->unsignedInteger('payment_receipt_file_id')->nullable()->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('treatment_bookings', function (Blueprint $table) {
            foreach ([
                'payment_receipt_file_id',
                'payment_status',
                'product_variations',
                'product_options',
                'variant_id',
            ] as $column) {
                if (Schema::hasColumn('treatment_bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
