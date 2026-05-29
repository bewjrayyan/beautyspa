<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id')->nullable()->unique();
            $table->unsignedInteger('beautician_id')->nullable();
            $table->unsignedInteger('treatment_category_id')->nullable();
            $table->unsignedInteger('product_id')->nullable();
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->date('appointment_date')->nullable();
            $table->string('appointment_time', 20)->nullable();
            $table->string('status', 20)->default('pending');
            $table->decimal('total', 18, 4)->nullable();
            $table->string('currency', 3)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['appointment_date', 'status']);
            $table->index(['beautician_id', 'appointment_date']);
            $table->index(['treatment_category_id', 'appointment_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_bookings');
    }
};
