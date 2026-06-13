<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Beautician\Entities\BeauticianJobTitle;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beautician_job_titles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $defaults = trans('beautician::beauticians.job_titles', [], 'en');

        if (is_array($defaults)) {
            $now = now();

            foreach (array_values($defaults) as $index => $name) {
                DB::table('beautician_job_titles')->insertOrIgnore([
                    'name' => $name,
                    'position' => $index,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (Schema::hasTable('beauticians')) {
            $customTitles = DB::table('beauticians')
                ->whereNotNull('job_title')
                ->where('job_title', '!=', '')
                ->distinct()
                ->pluck('job_title');

            $now = now();

            foreach ($customTitles as $name) {
                DB::table('beautician_job_titles')->insertOrIgnore([
                    'name' => $name,
                    'position' => 0,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        BeauticianJobTitle::clearCache();
    }


    public function down(): void
    {
        Schema::dropIfExists('beautician_job_titles');
    }
};
