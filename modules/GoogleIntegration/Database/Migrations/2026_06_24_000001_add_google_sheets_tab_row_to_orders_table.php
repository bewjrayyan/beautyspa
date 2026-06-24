<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('google_sheets_tab')->nullable()->after('google_sheets_synced_at');
            $table->unsignedInteger('google_sheets_row')->nullable()->after('google_sheets_tab');
        });
    }


    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['google_sheets_tab', 'google_sheets_row']);
        });
    }
};
