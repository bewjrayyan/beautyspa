<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Category\Entities\Category;
use Modules\Option\Entities\Option;
use Modules\Product\Entities\Product;
use Modules\Variation\Entities\Variation;

class BeautyTransformationTreatmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $variants = [
                ['name' => 'UPPER LIPS REDUCTION', 'price' => 6610.00],
                ['name' => 'DOWN LIPS REDUCTION', 'price' => 6050.00],
                ['name' => 'LIPS REDUCTION', 'price' => 8499.00],
                ['name' => 'NASAL TIP RESHAPING', 'price' => 8499.00],
                ['name' => 'ANGEL EYE REFINEMENT', 'price' => 8499.00],
                ['name' => 'VENUS REFINEMENT', 'price' => 8499.00],
                ['name' => 'SUBBROW LIFTING', 'price' => 8499.00],
                ['name' => 'FACELIFT MAJOR', 'price' => 9699.00],
                ['name' => 'FACELIFT MINOR', 'price' => 7299.00],
                ['name' => 'EYEBROW MICROSHADING CLASSIC', 'price' => 966.00],
                ['name' => 'EYEBROW MICROBLADING HAIR-STROKE', 'price' => 1099.00],
                ['name' => 'LIP BLUSH / LIP TINT EMBROIDERY', 'price' => 966.00],
                ['name' => 'UPPER EYELINER EMBROIDERY', 'price' => 966.00],
                ['name' => 'LOWER EYELINER EMBROIDERY', 'price' => 966.00],
            ];

            $product = Product::where('sku', 'BEAUTY_TRANSFORMATION_POSTER')->first();

            if ($product) {
                return;
            }

            $product = Product::create([
                'sku' => 'BEAUTY_TRANSFORMATION_POSTER',
                'price' => 966.00,
                'selling_price' => 966.00,
                'manage_stock' => 0,
                'qty' => 0,
                'in_stock' => 1,
                'is_active' => 1,
                'is_virtual' => 1,
                'en' => [
                    'name' => 'Beauty Transformation',
                    'description' => '<p><strong>Beauty Transformation – Imma Serilaris</strong></p><p>Aesthetic treatment collection covering facial refinement, lifting, reshaping and cosmetic embroidery services. Price list updated 7 September 2026.</p>',
                    'short_description' => 'Beauty Transformation aesthetic treatments with 14 selectable treatment variants.',
                ],
                'ms' => [
                    'name' => 'Beauty Transformation',
                    'description' => '<p><strong>Beauty Transformation – Imma Serilaris</strong></p><p>Koleksi rawatan estetik merangkumi refinement wajah, lifting, reshaping dan rawatan sulaman kosmetik. Senarai harga dikemas kini pada 7 September 2026.</p>',
                    'short_description' => 'Rawatan Beauty Transformation dengan 14 pilihan varian rawatan.',
                ],
            ]);

            $aestheticCategory = Category::where('slug', 'aesthetic-estetik')->first();
            if ($aestheticCategory) {
                $product->categories()->syncWithoutDetaching([$aestheticCategory->id]);
            }

            $variation = Variation::create([
                'uid' => Str::uuid()->toString(),
                'type' => 'text',
                'is_global' => false,
                'position' => 1,
                'en' => ['name' => 'Choose Treatment / Beauty Transformation Variant'],
                'ms' => ['name' => 'Pilih Rawatan / Varian Beauty Transformation'],
            ]);
            $product->variations()->attach($variation->id);

            $option = Option::create([
                'type' => 'dropdown',
                'is_required' => true,
                'is_global' => false,
                'position' => 1,
                'en' => ['name' => 'Choose Treatment / Beauty Transformation Variant'],
                'ms' => ['name' => 'Pilih Rawatan / Varian Beauty Transformation'],
            ]);
            $product->options()->attach($option->id);

            foreach ($variants as $index => $variantData) {
                $position = $index + 1;
                $uid = Str::uuid()->toString();

                $variation->values()->create([
                    'uid' => $uid,
                    'value' => '',
                    'position' => $position,
                    'en' => ['label' => $variantData['name']],
                    'ms' => ['label' => $variantData['name']],
                ]);

                $product->variants()->create([
                    'uid' => $uid,
                    'uids' => $uid,
                    'name' => $variantData['name'],
                    'sku' => 'beauty_transformation_' . Str::slug($variantData['name'], '_'),
                    'price' => $variantData['price'],
                    'selling_price' => $variantData['price'],
                    'manage_stock' => 0,
                    'qty' => 0,
                    'in_stock' => 1,
                    'is_active' => true,
                    'is_default' => ($position === 1),
                    'position' => $position,
                ]);

                $option->values()->create([
                    'price' => $variantData['price'],
                    'price_type' => 'fixed',
                    'position' => $position,
                    'en' => ['label' => $variantData['name']],
                    'ms' => ['label' => $variantData['name']],
                ]);
            }
        });
    }
}
