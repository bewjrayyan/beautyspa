<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Modules\Product\Entities\Product;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'shipping_class_id') || ! Schema::hasTable('shipping_classes')) {
            return;
        }

        $classId = DB::table('shipping_classes')->where('is_active', true)->orderBy('id')->value('id')
            ?? DB::table('shipping_classes')->orderBy('id')->value('id');

        if (! $classId) {
            return;
        }

        $slugs = Product::PHYSICAL_PRODUCT_SLUGS;

        DB::table('products')
            ->whereNull('shipping_class_id')
            ->where(function ($query) use ($slugs) {
                $query->where('is_virtual', false)->orWhereIn('slug', $slugs);
            })
            ->update(['shipping_class_id' => $classId]);
    }

    public function down(): void
    {
        // Intentionally left blank — do not unset assigned shipping classes.
    }
};
