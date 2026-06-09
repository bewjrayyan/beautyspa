<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')
            || Schema::hasColumn('products', 'treatment_category_id')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'is_virtual')) {
                $table->unsignedInteger('treatment_category_id')->nullable()->after('is_virtual');
            } else {
                $table->unsignedInteger('treatment_category_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')
            || ! Schema::hasColumn('products', 'treatment_category_id')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('treatment_category_id');
        });
    }
};
