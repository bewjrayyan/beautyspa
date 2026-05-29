<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onesender_message_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient', 64);
            $table->string('recipient_type', 16)->default('individual');
            $table->string('message_type', 16)->default('text');
            $table->char('message_hash', 64);
            $table->string('dedupe_key', 191)->nullable();
            $table->string('source', 191)->nullable();
            $table->text('message_preview')->nullable();
            $table->string('status', 32);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->json('api_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['recipient', 'message_hash', 'created_at']);
            $table->index(['dedupe_key', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('created_at');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('onesender_message_logs');
    }
};
