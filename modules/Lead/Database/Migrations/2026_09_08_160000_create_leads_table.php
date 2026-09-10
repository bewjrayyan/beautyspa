<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone', 32)->index();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('source', 64)->default('manual')->index();
            $table->string('status', 32)->default('new')->index();
            $table->unsignedInteger('spa_branch_id')->nullable()->index();
            $table->unsignedInteger('beautician_id')->nullable()->index();
            $table->unsignedInteger('customer_id')->nullable()->index();
            $table->boolean('is_duplicate')->default(false)->index();
            $table->boolean('is_existing_customer')->default(false)->index();
            $table->timestamp('last_followed_up_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('spa_branch_id')->references('id')->on('spa_branches')->nullOnDelete();
            $table->foreign('beautician_id')->references('id')->on('beauticians')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
