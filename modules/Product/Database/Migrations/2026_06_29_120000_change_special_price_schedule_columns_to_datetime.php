<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE products MODIFY special_price_start DATETIME NULL');
        DB::statement('ALTER TABLE products MODIFY special_price_end DATETIME NULL');
        DB::statement('ALTER TABLE product_variants MODIFY special_price_start DATETIME NULL');
        DB::statement('ALTER TABLE product_variants MODIFY special_price_end DATETIME NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE products MODIFY special_price_start DATE NULL');
        DB::statement('ALTER TABLE products MODIFY special_price_end DATE NULL');
        DB::statement('ALTER TABLE product_variants MODIFY special_price_start DATE NULL');
        DB::statement('ALTER TABLE product_variants MODIFY special_price_end DATE NULL');
    }
};
