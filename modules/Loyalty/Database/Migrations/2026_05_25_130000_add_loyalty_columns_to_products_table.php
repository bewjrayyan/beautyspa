<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('loyalty_bonus_points')
                ->default(0)
                ->after('is_active');

            $table->decimal('loyalty_earn_multiplier', 8, 2)
                ->default(1)
                ->after('loyalty_bonus_points');
        });
    }


    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['loyalty_bonus_points', 'loyalty_earn_multiplier']);
        });
    }
};
