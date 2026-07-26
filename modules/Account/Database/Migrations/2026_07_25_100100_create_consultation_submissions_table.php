<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_submissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('product_id')->index();
            $table->unsignedInteger('order_id')->nullable()->index();
            $table->unsignedInteger('order_product_id')->nullable()->unique();
            $table->unsignedInteger('template_version')->default(1);
            $table->string('form_title');
            $table->text('form_intro')->nullable();
            $table->text('consent_text');
            $table->json('questions_snapshot');
            $table->json('answers');
            $table->longText('signature_data');
            $table->boolean('consent_accepted')->default(false);
            $table->timestamp('submitted_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_submissions');
    }
};
