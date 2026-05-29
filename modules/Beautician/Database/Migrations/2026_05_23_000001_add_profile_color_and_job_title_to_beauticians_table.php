<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beauticians', function (Blueprint $table) {
            $table->string('profile_color', 7)->default('#6366f1')->after('phone');
            $table->string('job_title')->nullable()->after('profile_color');
        });
    }


    public function down(): void
    {
        Schema::table('beauticians', function (Blueprint $table) {
            $table->dropColumn(['profile_color', 'job_title']);
        });
    }
};
