<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('loyalty_points_earned')->default(0)->after('discount');
            $table->unsignedInteger('loyalty_points_redeemed')->default(0)->after('loyalty_points_earned');
            $table->decimal('loyalty_discount_amount', 18, 4)->unsigned()->default(0)->after('loyalty_points_redeemed');
        });
    }


    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'loyalty_points_earned',
                'loyalty_points_redeemed',
                'loyalty_discount_amount',
            ]);
        });
    }
};
