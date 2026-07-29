<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $orphanChecks = [
            'order_id' => 'orders',
            'beautician_id' => 'beauticians',
            'treatment_category_id' => 'categories',
            'product_id' => 'products',
        ];

        foreach ($orphanChecks as $column => $parentTable) {
            $orphans = DB::table('treatment_bookings as tb')
                ->whereNotNull("tb.{$column}")
                ->whereNotExists(function ($query) use ($column, $parentTable): void {
                    $query->selectRaw('1')
                        ->from($parentTable)
                        ->whereColumn("{$parentTable}.id", "tb.{$column}");
                })
                ->count();

            if ($orphans > 0) {
                throw new \RuntimeException(
                    "Cannot add treatment booking constraints: {$orphans} orphan {$column} value(s) require review."
                );
            }
        }

        Schema::table('treatment_bookings', function (Blueprint $table): void {
            $table->index(
                ['beautician_id', 'appointment_date', 'status', 'appointment_time'],
                'tb_beautician_date_status_time_idx'
            );

            $table->foreign('order_id', 'tb_order_fk')
                ->references('id')->on('orders')->nullOnDelete();
            $table->foreign('beautician_id', 'tb_beautician_fk')
                ->references('id')->on('beauticians')->nullOnDelete();
            $table->foreign('treatment_category_id', 'tb_category_fk')
                ->references('id')->on('categories')->nullOnDelete();
            $table->foreign('product_id', 'tb_product_fk')
                ->references('id')->on('products')->nullOnDelete();
        });

        Schema::table('treatment_bookings', function (Blueprint $table): void {
            $table->dropIndex('treatment_bookings_beautician_id_appointment_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('treatment_bookings', function (Blueprint $table): void {
            $table->index(
                ['beautician_id', 'appointment_date'],
                'treatment_bookings_beautician_id_appointment_date_index'
            );

            $table->dropForeign('tb_order_fk');
            $table->dropForeign('tb_beautician_fk');
            $table->dropForeign('tb_category_fk');
            $table->dropForeign('tb_product_fk');
            $table->dropIndex('tb_beautician_date_status_time_idx');
        });
    }
};
