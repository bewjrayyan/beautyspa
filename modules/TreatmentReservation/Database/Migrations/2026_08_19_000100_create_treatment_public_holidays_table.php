<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('treatment_public_holidays')) {
            return;
        }

        Schema::create('treatment_public_holidays', function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->date('date');
            $table->string('name', 255);
            $table->string('day_name', 30)->nullable();
            $table->json('state_codes')->nullable();
            $table->boolean('is_subject_to_change')->default(false);

            // Used by the calendar UI to color the watermark/badges consistently.
            $table->string('color', 32)->default('#3b82f6');

            // e.g. "malaysia-holiday-api:v1"
            $table->string('source', 100)->default('malaysia-holiday-api');

            $table->timestamps();

            $table->unique(['date', 'name', 'source'], 'treatment_public_holidays_unique');
            $table->index(['date'], 'treatment_public_holidays_date_idx');
        });

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_public_holidays');
    }
};

