<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('spa_branch_id')
                ->nullable()
                ->after('appointment_time')
                ->index();

            $table->foreign('spa_branch_id')
                ->references('id')
                ->on('spa_branches')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['spa_branch_id']);
            $table->dropColumn('spa_branch_id');
        });
    }
};
