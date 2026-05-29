<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('beautician_id')->nullable()->after('note');
            $table->date('appointment_date')->nullable()->after('beautician_id');
            $table->string('appointment_time', 20)->nullable()->after('appointment_date');
        });
    }


    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['beautician_id', 'appointment_date', 'appointment_time']);
        });
    }
};
