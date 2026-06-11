<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_stamp_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('reward_description')->nullable();
            $table->unsignedSmallInteger('stamps_required')->default(7);
            $table->unsignedSmallInteger('validity_days')->default(30);
            $table->boolean('virtual_treatments_only')->default(true);
            $table->json('product_ids')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('loyalty_stamp_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreignId('program_id')->constrained('loyalty_stamp_programs')->cascadeOnDelete();
            $table->unsignedSmallInteger('stamps_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'program_id']);
        });

        Schema::create('loyalty_stamp_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('loyalty_stamp_wallets')->cascadeOnDelete();
            $table->unsignedInteger('order_id');
            $table->unsignedSmallInteger('stamps_added')->default(1);
            $table->timestamps();

            $table->unique(['order_id', 'wallet_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('loyalty_stamp_entries');
        Schema::dropIfExists('loyalty_stamp_wallets');
        Schema::dropIfExists('loyalty_stamp_programs');
    }
};
