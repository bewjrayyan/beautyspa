<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('products', 'is_virtual')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_virtual')->default(false)->before('is_active');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasColumn('products', 'is_virtual')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_virtual');
        });
    }
};
