<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_tier_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('from_tier_id')->nullable();
            $table->unsignedInteger('to_tier_id');
            $table->string('reason', 64);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('user_id');
        });

        Schema::create('loyalty_redemption_holds', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('cart_id', 128);
            $table->unsignedInteger('points');
            $table->decimal('discount_amount', 18, 4)->unsigned();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'cart_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('loyalty_redemption_holds');
        Schema::dropIfExists('loyalty_tier_history');
    }
};
