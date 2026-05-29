<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('wallet_id');
            $table->string('type', 32);
            $table->integer('points');
            $table->unsignedInteger('balance_after');
            $table->string('reference_type', 64)->nullable();
            $table->string('reference_id', 128)->nullable();
            $table->string('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('wallet_id')->references('id')->on('loyalty_wallets')->cascadeOnDelete();
            $table->unique(['wallet_id', 'reference_type', 'reference_id', 'type'], 'loyalty_tx_idempotent');
            $table->index(['wallet_id', 'created_at']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
