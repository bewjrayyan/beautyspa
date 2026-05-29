<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beautician_blocked_times', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('beautician_id');
            $table->date('block_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('beautician_id')
                ->references('id')
                ->on('beauticians')
                ->cascadeOnDelete();

            $table->index(['beautician_id', 'block_date'], 'beautician_blocked_times_date_idx');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('beautician_blocked_times');
    }
};
