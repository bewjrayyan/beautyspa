<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'checkout_treatment_lines')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->json('checkout_treatment_lines')->nullable()->after('note');
        });
    }


    public function down(): void
    {
        if (! Schema::hasColumn('orders', 'checkout_treatment_lines')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('checkout_treatment_lines');
        });
    }
};
