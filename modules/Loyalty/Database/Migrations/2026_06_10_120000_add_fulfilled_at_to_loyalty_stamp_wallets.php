<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loyalty_stamp_wallets', function (Blueprint $table) {
            $table->timestamp('fulfilled_at')->nullable()->after('redemption_code');
            $table->unsignedInteger('fulfilled_by')->nullable()->after('fulfilled_at');
        });
    }


    public function down(): void
    {
        Schema::table('loyalty_stamp_wallets', function (Blueprint $table) {
            $table->dropColumn(['fulfilled_at', 'fulfilled_by']);
        });
    }
};
