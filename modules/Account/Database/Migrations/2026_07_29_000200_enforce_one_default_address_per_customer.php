<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateCustomer = DB::table('default_addresses')
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) > 1')
            ->limit(1)
            ->value('customer_id');

        if ($duplicateCustomer !== null) {
            throw new \RuntimeException(
                'Duplicate default address rows require review before the unique constraint can be added.'
            );
        }

        Schema::table('default_addresses', function (Blueprint $table): void {
            $table->unique('customer_id', 'default_addresses_customer_unique');
        });
    }

    public function down(): void
    {
        Schema::table('default_addresses', function (Blueprint $table): void {
            $table->dropUnique('default_addresses_customer_unique');
        });
    }
};
