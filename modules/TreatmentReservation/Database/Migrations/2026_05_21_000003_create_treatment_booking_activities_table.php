<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_booking_activities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('treatment_booking_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('action', 50);
            $table->text('from_value')->nullable();
            $table->text('to_value')->nullable();
            $table->timestamps();

            $table->foreign('treatment_booking_id')
                ->references('id')
                ->on('treatment_bookings')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['treatment_booking_id', 'created_at'], 'tr_booking_activities_booking_created_idx');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('treatment_booking_activities');
    }
};
