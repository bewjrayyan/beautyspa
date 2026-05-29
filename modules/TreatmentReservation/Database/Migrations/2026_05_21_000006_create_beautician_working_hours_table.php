<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beautician_working_hours', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('beautician_id');
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->foreign('beautician_id')
                ->references('id')
                ->on('beauticians')
                ->cascadeOnDelete();

            $table->unique(['beautician_id', 'day_of_week'], 'beautician_working_hours_unique');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('beautician_working_hours');
    }
};
