<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_bookings', function (Blueprint $table) {
            $table->unsignedInteger('coupon_id')->nullable()->after('total')->index();
            $table->string('coupon_code', 100)->nullable()->after('coupon_id');
            $table->decimal('coupon_discount', 18, 4)->unsigned()->default(0)->after('coupon_code');
            $table->unsignedInteger('loyalty_points_redeemed')->default(0)->after('coupon_discount');
            $table->decimal('loyalty_discount_amount', 18, 4)->unsigned()->default(0)->after('loyalty_points_redeemed');
        });
    }

    public function down(): void
    {
        Schema::table('treatment_bookings', function (Blueprint $table) {
            $table->dropIndex(['coupon_id']);
            $table->dropColumn(['coupon_id', 'coupon_code', 'coupon_discount', 'loyalty_points_redeemed', 'loyalty_discount_amount']);
        });
    }
};
