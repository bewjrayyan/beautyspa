<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onesender_outbound_queue', function (Blueprint $table) {
            $table->id();
            $table->string('recipient', 64);
            $table->string('recipient_type', 16)->default('individual');
            $table->string('message_type', 16)->default('text');
            $table->char('message_hash', 64);
            $table->string('dedupe_key', 191)->nullable();
            $table->string('source', 191)->nullable();
            $table->text('message_preview')->nullable();
            $table->json('payload');
            $table->string('status', 32)->default('pending');
            $table->timestamp('scheduled_at')->useCurrent();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index(['dedupe_key', 'status']);
            $table->index(['recipient', 'message_hash', 'status']);
            $table->index('created_at');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('onesender_outbound_queue');
    }
};
