<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_wallets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->unique();
            $table->unsignedInteger('tier_id');
            $table->unsignedInteger('balance')->default(0);
            $table->decimal('lifetime_spend', 18, 4)->unsigned()->default(0);
            $table->timestamp('tier_assigned_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('tier_id')->references('id')->on('loyalty_tiers');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('loyalty_wallets');
    }
};
