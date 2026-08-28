<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('default_addresses', function (Blueprint $table): void {
            if (! Schema::hasColumn('default_addresses', 'shipping_address_id')) {
                $table->unsignedInteger('shipping_address_id')->nullable()->after('address_id');
                $table->foreign('shipping_address_id')
                    ->references('id')
                    ->on('addresses')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('default_addresses', function (Blueprint $table): void {
            if (Schema::hasColumn('default_addresses', 'shipping_address_id')) {
                $table->dropForeign(['shipping_address_id']);
                $table->dropColumn('shipping_address_id');
            }
        });
    }
};
