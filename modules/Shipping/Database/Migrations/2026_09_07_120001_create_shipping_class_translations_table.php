<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_class_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shipping_class_id')->unsigned();
            $table->string('locale');
            $table->string('name');

            $table->unique(['shipping_class_id', 'locale']);
            $table->foreign('shipping_class_id')->references('id')->on('shipping_classes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_class_translations');
    }
};
