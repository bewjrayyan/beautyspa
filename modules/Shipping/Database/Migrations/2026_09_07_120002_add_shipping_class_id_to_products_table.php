<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('shipping_class_id')->unsigned()->nullable()->after('tax_class_id');
            $table->foreign('shipping_class_id')->references('id')->on('shipping_classes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['shipping_class_id']);
            $table->dropColumn('shipping_class_id');
        });
    }
};
