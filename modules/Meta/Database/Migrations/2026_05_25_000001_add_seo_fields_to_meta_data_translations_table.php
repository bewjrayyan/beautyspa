<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meta_data_translations', function (Blueprint $table) {
            $table->unsignedInteger('og_image_id')->nullable()->after('meta_description');
            $table->string('meta_robots', 32)->default('index, follow')->after('og_image_id');
        });
    }


    public function down(): void
    {
        Schema::table('meta_data_translations', function (Blueprint $table) {
            $table->dropColumn(['og_image_id', 'meta_robots']);
        });
    }
};
