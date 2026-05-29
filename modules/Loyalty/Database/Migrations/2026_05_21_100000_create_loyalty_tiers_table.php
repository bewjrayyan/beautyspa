<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('name');
            $table->decimal('min_lifetime_spend', 18, 4)->unsigned()->default(0);
            $table->decimal('earn_multiplier', 8, 4)->unsigned()->default(1);
            $table->json('benefits')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('loyalty_tiers');
    }
};
