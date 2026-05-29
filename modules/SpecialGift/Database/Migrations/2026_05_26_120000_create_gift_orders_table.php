<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id')->unique();
            $table->unsignedInteger('sender_user_id')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->text('gift_message')->nullable();
            $table->string('gift_code', 32)->unique();
            $table->string('status', 32)->default('pending');
            $table->timestamp('recipient_notified_at')->nullable();
            $table->timestamp('sender_notified_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('sender_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('gift_orders');
    }
};
