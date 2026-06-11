<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loyalty_stamp_wallets', function (Blueprint $table) {
            $table->string('redemption_code', 32)->nullable()->unique()->after('redeemed_at');
        });
    }


    public function down(): void
    {
        Schema::table('loyalty_stamp_wallets', function (Blueprint $table) {
            $table->dropColumn('redemption_code');
        });
    }
};
