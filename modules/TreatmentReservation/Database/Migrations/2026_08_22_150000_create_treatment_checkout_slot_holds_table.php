<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('treatment_checkout_slot_holds')) {
            return;
        }

        Schema::create('treatment_checkout_slot_holds', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('beautician_id');
            $table->date('appointment_date');
            $table->char('appointment_time', 5);
            $table->unsignedInteger('product_id')->nullable();
            $table->unsignedInteger('spa_branch_id')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['beautician_id', 'appointment_date', 'expires_at'], 'checkout_slot_holds_beautician_date_exp');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('treatment_checkout_slot_holds');
    }
};
