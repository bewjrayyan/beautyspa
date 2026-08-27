<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_birthday_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->index();
            $table->unsignedSmallInteger('year');
            $table->string('phone', 32)->nullable();
            $table->string('reward_type', 32)->default('none');
            $table->json('reward_payload')->nullable();
            $table->string('coupon_code', 64)->nullable();
            $table->unsignedInteger('coupon_id')->nullable();
            $table->unsignedInteger('points_awarded')->nullable();
            $table->text('message_preview')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->string('delivery_status', 32)->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'year']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('whatsapp_birthday_reminder_logs');
    }
};
