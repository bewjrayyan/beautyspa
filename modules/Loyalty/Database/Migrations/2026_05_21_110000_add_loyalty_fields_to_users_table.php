<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->string('referral_code', 16)->nullable()->unique()->after('date_of_birth');
            $table->unsignedInteger('referred_by_user_id')->nullable()->index()->after('referral_code');
        });
    }


    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth', 'referral_code', 'referred_by_user_id']);
        });
    }
};
