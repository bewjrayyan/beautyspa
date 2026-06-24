<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_sheets_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id')->nullable()->index();
            $table->string('trigger', 32)->default('auto');
            $table->string('status', 16);
            $table->string('sheet_tab', 100)->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('google_sheets_sync_logs');
    }
};
