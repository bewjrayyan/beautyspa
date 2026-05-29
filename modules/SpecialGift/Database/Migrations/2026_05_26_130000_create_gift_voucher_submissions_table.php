<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gift_orders')) {
            Schema::drop('gift_orders');
        }

        if (Schema::hasColumn('products', 'is_gift')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('is_gift');
            });
        }

        if (! Schema::hasTable('gift_voucher_submissions')) {
            Schema::create('gift_voucher_submissions', function (Blueprint $table) {
                $table->id();
                $table->string('recipient_name');
                $table->string('order_number', 64);
                $table->unsignedInteger('order_id')->nullable();
                $table->string('whatsapp_number', 32);
                $table->string('sender_name')->nullable();
                $table->string('generated_image_url', 500)->nullable();
                $table->string('delivery_status', 32)->default('processing');
                $table->text('whatsapp_response')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->index('order_number');
                $table->index('delivery_status');
                $table->index('created_at');
            });
        }
    }


    public function down(): void
    {
        Schema::dropIfExists('gift_voucher_submissions');
    }
};
