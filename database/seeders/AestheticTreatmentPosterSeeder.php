<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Category\Entities\Category;
use Modules\Option\Entities\Option;
use Modules\Option\Entities\OptionValue;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Variation\Entities\Variation;
use Modules\Variation\Entities\VariationValue;

class AestheticTreatmentPosterSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $aestheticCategory = Category::where('slug', 'aesthetic-estetik')->first();
            $benangCategory = Category::where('slug', 'benang')->first();
            $lipoCategory = Category::where('slug', 'lipo')->first();

            // -------------------------------------------------------------
            // POSTER 1: RoyalLift (Aesthetic Treatment)
            // -------------------------------------------------------------
            $royalLiftVariantsData = [
                ['name' => 'ROYAL LIFT FOX EYES 8PCS', 'price' => 6050.00],
                ['name' => 'ROYAL LIFT FOX EYES 10PCS', 'price' => 8500.00],
                ['name' => 'ROYAL LIFT DOUBLE EYELID', 'price' => 7850.00],
                ['name' => 'ROYAL LIFT PDO DOUBLE CHIN 4PCS', 'price' => 1210.00],
                ['name' => 'ROYAL LIFT PDO DOUBLE CHIN 8PCS', 'price' => 2300.00],
                ['name' => 'ROYAL LIFT PDO DOUBLE CHIN 10PCS', 'price' => 2800.00],
                ['name' => 'ROYAL LIFT MUKA PDO 40PCS', 'price' => 2300.00],
                ['name' => 'ROYAL LIFT MUKA PDO 60PCS', 'price' => 3200.00],
                ['name' => 'ROYAL LIFT MUKA PDO 80PCS', 'price' => 3900.00],
                ['name' => 'ROYAL LIFT COLLAGEN 30 PCS', 'price' => 850.00],
                ['name' => 'ROYAL LIFT COLLAGEN 50 PCS', 'price' => 1399.00],
                ['name' => 'ROYAL LIFT (MONO) LIPS', 'price' => 1099.00],
                ['name' => 'ROYAL LIFT HIDUNG (2PCS)', 'price' => 600.00],
                ['name' => 'ROYAL LIFT HIDUNG (4PCS)', 'price' => 1210.00],
                ['name' => 'ROYAL LIFT HIDUNG (6PCS)', 'price' => 1700.00],
                ['name' => 'ROYAL LIFT HIDUNG (8PCS)', 'price' => 2199.00],
                ['name' => 'ROYAL LIFT HIDUNG (10PCS)', 'price' => 2660.00],
                ['name' => 'ROYAL LIFT HIDUNG (12PCS)', 'price' => 3400.00],
                ['name' => 'ROYAL LIFT MUKA (2PCS)', 'price' => 850.00],
                ['name' => 'ROYAL LIFT MUKA (4PCS)', 'price' => 1599.00],
                ['name' => 'ROYAL LIFT MUKA (6PCS)', 'price' => 2300.00],
                ['name' => 'ROYAL LIFT MUKA (8PCS)', 'price' => 2900.00],
                ['name' => 'ROYAL LIFT MUKA (10PCS)', 'price' => 3510.00],
                ['name' => 'ROYAL LIFT MUKA (12PCS)', 'price' => 3999.00],
                ['name' => 'ROYAL LIFT TIPS NOSE (2PCS)', 'price' => 799.00],
                ['name' => 'ROYAL LIFT TIPS NOSE (4PCS)', 'price' => 1599.00],
                ['name' => 'ROYAL LIFT TIPS NOSE (6PCS)', 'price' => 2399.00],
                ['name' => 'ROYAL LIFT TIPS NOSE (8PCS)', 'price' => 3199.00],
                ['name' => 'ROYAL LIFT TIPS NOSE (10PCS)', 'price' => 4360.00],
                ['name' => 'ROYAL LIFT TIPS NOSE (12PCS)', 'price' => 4850.00],
                ['name' => 'DIMPLE 1 Side', 'price' => 1450.00],
                ['name' => 'DIMPLE 2 Side', 'price' => 2660.00],
            ];

            $royalLiftProduct = Product::where('sku', 'ROYAL_LIFT_POSTER')->first();

            if (! $royalLiftProduct) {
                $royalLiftProduct = Product::create([
                    'sku' => 'ROYAL_LIFT_POSTER',
                    'price' => 600.00,
                    'selling_price' => 600.00,
                    'manage_stock' => 0,
                    'qty' => 0,
                    'in_stock' => 1,
                    'is_active' => 1,
                    'is_virtual' => 1,
                    'en' => [
                        'name' => 'RoyalLift',
                        'description' => '<p><strong>RoyalLift – Senarai Rawatan Estetika Benang Premium (Imma Serilaris)</strong></p><p>Rawatan RoyalLift ialah rawatan aesthetic termaju berasaskan benang khas (PDO / Collagen / Lift Thread) untuk mengencang, menegangkan, dan membentuk kontur muka serta kawasan khusus seperti mata, hidung, dagu, dan bibir tanpa pembedahan.</p>',
                        'short_description' => 'Senarai rawatan estetika RoyalLift termaju (Fox Eyes, Double Eyelid, Double Chin, PDO Muka, Collagen, Hidung, Tips Nose, Dimple).',
                    ],
                    'ms' => [
                        'name' => 'RoyalLift',
                        'description' => '<p><strong>RoyalLift – Senarai Rawatan Estetika Benang Premium (Imma Serilaris)</strong></p><p>Rawatan RoyalLift ialah rawatan aesthetic termaju berasaskan benang khas (PDO / Collagen / Lift Thread) untuk mengencang, menegangkan, dan membentuk kontur muka serta kawasan khusus seperti mata, hidung, dagu, dan bibir tanpa pembedahan.</p>',
                        'short_description' => 'Senarai rawatan estetika RoyalLift termaju (Fox Eyes, Double Eyelid, Double Chin, PDO Muka, Collagen, Hidung, Tips Nose, Dimple).',
                    ],
                ]);

                if ($aestheticCategory || $benangCategory) {
                    $royalLiftProduct->categories()->sync(array_filter([$aestheticCategory?->id, $benangCategory?->id]));
                }

                $royalLiftVariation = Variation::create([
                    'uid' => Str::uuid()->toString(),
                    'type' => 'text',
                    'is_global' => false,
                    'position' => 1,
                    'en' => ['name' => 'Pilih Rawatan / Varian RoyalLift'],
                    'ms' => ['name' => 'Pilih Rawatan / Varian RoyalLift'],
                ]);
                $royalLiftProduct->variations()->attach($royalLiftVariation->id);

                $royalLiftOption = Option::create([
                    'type' => 'dropdown',
                    'is_required' => true,
                    'is_global' => false,
                    'position' => 1,
                    'en' => ['name' => 'Pilih Rawatan / Varian RoyalLift'],
                    'ms' => ['name' => 'Pilih Rawatan / Varian RoyalLift'],
                ]);
                $royalLiftProduct->options()->attach($royalLiftOption->id);

                $pos = 0;
                foreach ($royalLiftVariantsData as $varData) {
                    $pos++;
                    $valUid = Str::uuid()->toString();

                    $royalLiftVariation->values()->create([
                        'uid' => $valUid,
                        'value' => '',
                        'position' => $pos,
                        'en' => ['label' => $varData['name']],
                        'ms' => ['label' => $varData['name']],
                    ]);

                    $royalLiftProduct->variants()->create([
                        'uid' => $valUid,
                        'uids' => $valUid,
                        'name' => $varData['name'],
                        'sku' => Str::slug($varData['name'], '_'),
                        'price' => $varData['price'],
                        'selling_price' => $varData['price'],
                        'manage_stock' => 0,
                        'qty' => 0,
                        'in_stock' => 1,
                        'is_active' => true,
                        'is_default' => ($pos === 1),
                        'position' => $pos,
                    ]);

                    $royalLiftOption->values()->create([
                        'price' => $varData['price'],
                        'price_type' => 'fixed',
                        'position' => $pos,
                        'en' => ['label' => $varData['name']],
                        'ms' => ['label' => $varData['name']],
                    ]);
                }
            }

            // -------------------------------------------------------------
            // POSTER 2: AuraCurve (Aesthetic Treatment)
            // -------------------------------------------------------------
            $auraCurveVariantsData = [
                ['name' => 'AURA CURVE 100ML', 'price' => 967.00],
                ['name' => 'AURA CURVE 300ML', 'price' => 2903.00],
                ['name' => 'AURA CURVE 400ML', 'price' => 3508.00],
                ['name' => 'AURA CURVE 600ML', 'price' => 4839.00],
                ['name' => 'AURA CURVE 1000ML', 'price' => 7259.00],
                ['name' => 'AURA CURVE 2000ML', 'price' => 14035.00],
                ['name' => 'AURA LIPS', 'price' => 1450.00],
                ['name' => 'AURA EYEBAG', 'price' => 1450.00],
                ['name' => 'AURA MISS V', 'price' => 3145.00],
                ['name' => 'AURA CHIN', 'price' => 1209.00],
                ['name' => 'AURA SMILE LINE', 'price' => 1450.00],
                ['name' => 'AURA MUKA', 'price' => 1330.00],
                ['name' => 'SERVICE AURA', 'price' => 363.00],
                ['name' => 'CAIR/REMOVE KETUL AURA', 'price' => 778.00],
            ];

            $auraCurveProduct = Product::where('sku', 'AURA_CURVE_POSTER')->first();

            if (! $auraCurveProduct) {
                $auraCurveProduct = Product::create([
                    'sku' => 'AURA_CURVE_POSTER',
                    'price' => 363.00,
                    'selling_price' => 363.00,
                    'manage_stock' => 0,
                    'qty' => 0,
                    'in_stock' => 1,
                    'is_active' => 1,
                    'is_virtual' => 1,
                    'en' => [
                        'name' => 'AuraCurve',
                        'description' => '<p><strong>AuraCurve – Senarai Rawatan Estetika Kontur Badan & Wajah (Imma Serilaris)</strong></p><p>Rawatan AuraCurve ialah rawatan pembentukan kontur badan dan wajah (body & facial contouring / volume filling / slimming) untuk mengurangkan lemak tepu, menegangkan kulit, dan menambah isipadu kawasan estetika.</p>',
                        'short_description' => 'Senarai rawatan estetika AuraCurve termaju (Aura Curve 100ml-2000ml, Lips, Eyebag, Miss V, Chin, Smile Line, Muka, Service Aura).',
                    ],
                    'ms' => [
                        'name' => 'AuraCurve',
                        'description' => '<p><strong>AuraCurve – Senarai Rawatan Estetika Kontur Badan & Wajah (Imma Serilaris)</strong></p><p>Rawatan AuraCurve ialah rawatan pembentukan kontur badan dan wajah (body & facial contouring / volume filling / slimming) untuk mengurangkan lemak tepu, menegangkan kulit, dan menambah isipadu kawasan estetika.</p>',
                        'short_description' => 'Senarai rawatan estetika AuraCurve termaju (Aura Curve 100ml-2000ml, Lips, Eyebag, Miss V, Chin, Smile Line, Muka, Service Aura).',
                    ],
                ]);

                if ($aestheticCategory || $lipoCategory) {
                    $auraCurveProduct->categories()->sync(array_filter([$aestheticCategory?->id, $lipoCategory?->id]));
                }

                $auraCurveVariation = Variation::create([
                    'uid' => Str::uuid()->toString(),
                    'type' => 'text',
                    'is_global' => false,
                    'position' => 1,
                    'en' => ['name' => 'Pilih Rawatan / Varian AuraCurve'],
                    'ms' => ['name' => 'Pilih Rawatan / Varian AuraCurve'],
                ]);
                $auraCurveProduct->variations()->attach($auraCurveVariation->id);

                $auraCurveOption = Option::create([
                    'type' => 'dropdown',
                    'is_required' => true,
                    'is_global' => false,
                    'position' => 1,
                    'en' => ['name' => 'Pilih Rawatan / Varian AuraCurve'],
                    'ms' => ['name' => 'Pilih Rawatan / Varian AuraCurve'],
                ]);
                $auraCurveProduct->options()->attach($auraCurveOption->id);

                $pos = 0;
                foreach ($auraCurveVariantsData as $varData) {
                    $pos++;
                    $valUid = Str::uuid()->toString();

                    $auraCurveVariation->values()->create([
                        'uid' => $valUid,
                        'value' => '',
                        'position' => $pos,
                        'en' => ['label' => $varData['name']],
                        'ms' => ['label' => $varData['name']],
                    ]);

                    $auraCurveProduct->variants()->create([
                        'uid' => $valUid,
                        'uids' => $valUid,
                        'name' => $varData['name'],
                        'sku' => Str::slug($varData['name'], '_'),
                        'price' => $varData['price'],
                        'selling_price' => $varData['price'],
                        'manage_stock' => 0,
                        'qty' => 0,
                        'in_stock' => 1,
                        'is_active' => true,
                        'is_default' => ($pos === 1),
                        'position' => $pos,
                    ]);

                    $auraCurveOption->values()->create([
                        'price' => $varData['price'],
                        'price_type' => 'fixed',
                        'position' => $pos,
                        'en' => ['label' => $varData['name']],
                        'ms' => ['label' => $varData['name']],
                    ]);
                }
            }
        });
    }
}
