<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beautician_spa_branch', function (Blueprint $table) {
            $table->unsignedInteger('beautician_id');
            $table->unsignedInteger('spa_branch_id');

            $table->primary(['beautician_id', 'spa_branch_id']);

            $table->foreign('beautician_id')
                ->references('id')
                ->on('beauticians')
                ->cascadeOnDelete();

            $table->foreign('spa_branch_id')
                ->references('id')
                ->on('spa_branches')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beautician_spa_branch');
    }
};
