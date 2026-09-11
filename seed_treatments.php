<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\Category;
use Modules\Variation\Entities\Variation;
use Modules\Option\Entities\Option;

DB::transaction(function () {

    $aestheticCategory = Category::where('slug', 'aesthetic-estetik')->first();
    $benangCategory    = Category::where('slug', 'benang')->first();
    $lipoCategory      = Category::where('slug', 'lipo')->first();

    echo "Categories found: aesthetic=" . ($aestheticCategory ? $aestheticCategory->id : 'NO') 
       . " benang=" . ($benangCategory ? $benangCategory->id : 'NO') 
       . " lipo=" . ($lipoCategory ? $lipoCategory->id : 'NO') . "\n";

    // ─── ROYALLIFT ───────────────────────────────────────────
    $rlVariants = [
        ['name' => 'ROYAL LIFT FOX EYES 8PCS',           'price' => 6050],
        ['name' => 'ROYAL LIFT FOX EYES 10PCS',          'price' => 8500],
        ['name' => 'ROYAL LIFT DOUBLE EYELID',            'price' => 7850],
        ['name' => 'ROYAL LIFT PDO DOUBLE CHIN 4PCS',     'price' => 1210],
        ['name' => 'ROYAL LIFT PDO DOUBLE CHIN 8PCS',     'price' => 2300],
        ['name' => 'ROYAL LIFT PDO DOUBLE CHIN 10PCS',    'price' => 2800],
        ['name' => 'ROYAL LIFT MUKA PDO 40PCS',           'price' => 2300],
        ['name' => 'ROYAL LIFT MUKA PDO 60PCS',           'price' => 3200],
        ['name' => 'ROYAL LIFT MUKA PDO 80PCS',           'price' => 3900],
        ['name' => 'ROYAL LIFT COLLAGEN 30 PCS',          'price' => 850],
        ['name' => 'ROYAL LIFT COLLAGEN 50 PCS',          'price' => 1399],
        ['name' => 'ROYAL LIFT (MONO) LIPS',              'price' => 1099],
        ['name' => 'ROYAL LIFT HIDUNG (2PCS)',             'price' => 600],
        ['name' => 'ROYAL LIFT HIDUNG (4PCS)',             'price' => 1210],
        ['name' => 'ROYAL LIFT HIDUNG (6PCS)',             'price' => 1700],
        ['name' => 'ROYAL LIFT HIDUNG (8PCS)',             'price' => 2199],
        ['name' => 'ROYAL LIFT HIDUNG (10PCS)',            'price' => 2660],
        ['name' => 'ROYAL LIFT HIDUNG (12PCS)',            'price' => 3400],
        ['name' => 'ROYAL LIFT MUKA (2PCS)',               'price' => 850],
        ['name' => 'ROYAL LIFT MUKA (4PCS)',               'price' => 1599],
        ['name' => 'ROYAL LIFT MUKA (6PCS)',               'price' => 2300],
        ['name' => 'ROYAL LIFT MUKA (8PCS)',               'price' => 2900],
        ['name' => 'ROYAL LIFT MUKA (10PCS)',              'price' => 3510],
        ['name' => 'ROYAL LIFT MUKA (12PCS)',              'price' => 3999],
        ['name' => 'ROYAL LIFT TIPS NOSE (2PCS)',          'price' => 799],
        ['name' => 'ROYAL LIFT TIPS NOSE (4PCS)',          'price' => 1599],
        ['name' => 'ROYAL LIFT TIPS NOSE (6PCS)',          'price' => 2399],
        ['name' => 'ROYAL LIFT TIPS NOSE (8PCS)',          'price' => 3199],
        ['name' => 'ROYAL LIFT TIPS NOSE (10PCS)',         'price' => 4360],
        ['name' => 'ROYAL LIFT TIPS NOSE (12PCS)',         'price' => 4850],
        ['name' => 'DIMPLE 1 Side',                        'price' => 1450],
        ['name' => 'DIMPLE 2 Side',                        'price' => 2660],
    ];

    $rl = Product::where('sku', 'ROYAL_LIFT_POSTER')->first();
    if (!$rl) {
        $rl = Product::create([
            'sku'            => 'ROYAL_LIFT_POSTER',
            'price'          => 600,
            'selling_price'  => 600,
            'manage_stock'   => 0,
            'qty'            => 0,
            'in_stock'       => 1,
            'is_active'      => 1,
            'is_virtual'     => 1,
            'en' => [
                'name'              => 'RoyalLift',
                'description'       => '<p><strong>RoyalLift – Senarai Rawatan Estetika Benang Premium (Imma Serilaris)</strong></p><p>Rawatan RoyalLift ialah rawatan aesthetic termaju berasaskan benang khas (PDO / Collagen / Lift Thread) untuk mengencang, menegangkan, dan membentuk kontur muka serta kawasan khusus seperti mata, hidung, dagu, dan bibir tanpa pembedahan.</p>',
                'short_description' => 'Senarai rawatan estetika RoyalLift termaju (Fox Eyes, Double Eyelid, Double Chin, PDO Muka, Collagen, Hidung, Tips Nose, Dimple).',
            ],
            'ms' => [
                'name'              => 'RoyalLift',
                'description'       => '<p><strong>RoyalLift – Senarai Rawatan Estetika Benang Premium (Imma Serilaris)</strong></p><p>Rawatan RoyalLift ialah rawatan aesthetic termaju berasaskan benang khas (PDO / Collagen / Lift Thread) untuk mengencang, menegangkan, dan membentuk kontur muka serta kawasan khusus seperti mata, hidung, dagu, dan bibir tanpa pembedahan.</p>',
                'short_description' => 'Senarai rawatan estetika RoyalLift termaju (Fox Eyes, Double Eyelid, Double Chin, PDO Muka, Collagen, Hidung, Tips Nose, Dimple).',
            ],
        ]);
        echo "Created RoyalLift product ID={$rl->id}\n";
    } else {
        echo "RoyalLift already exists ID={$rl->id}\n";
    }

    // Attach categories
    $catIds = array_filter([$aestheticCategory?->id, $benangCategory?->id]);
    if (!empty($catIds)) {
        $rl->categories()->syncWithoutDetaching($catIds);
        echo "  Attached " . count($catIds) . " categories\n";
    }

    // Create variation, option, and variants if none exist
    if ($rl->variants()->count() === 0) {
        $variation = Variation::create([
            'uid'       => Str::uuid()->toString(),
            'type'      => 'text',
            'is_global' => false,
            'position'  => 1,
            'en'        => ['name' => 'Pilih Rawatan / Varian RoyalLift'],
            'ms'        => ['name' => 'Pilih Rawatan / Varian RoyalLift'],
        ]);
        $rl->variations()->attach($variation->id);

        $option = Option::create([
            'type'        => 'dropdown',
            'is_required' => true,
            'is_global'   => false,
            'position'    => 1,
            'en'          => ['name' => 'Pilih Rawatan / Varian RoyalLift'],
            'ms'          => ['name' => 'Pilih Rawatan / Varian RoyalLift'],
        ]);
        $rl->options()->attach($option->id);

        $pos = 0;
        foreach ($rlVariants as $v) {
            $pos++;
            $uid = Str::uuid()->toString();

            $variation->values()->create([
                'uid'      => $uid,
                'value'    => '',
                'position' => $pos,
                'en'       => ['label' => $v['name']],
                'ms'       => ['label' => $v['name']],
            ]);

            $rl->variants()->create([
                'uid'           => $uid,
                'uids'          => $uid,
                'name'          => $v['name'],
                'sku'           => Str::slug($v['name'], '_'),
                'price'         => $v['price'],
                'selling_price' => $v['price'],
                'manage_stock'  => 0,
                'qty'           => 0,
                'in_stock'      => 1,
                'is_active'     => true,
                'is_default'    => ($pos === 1),
                'position'      => $pos,
            ]);

            $option->values()->create([
                'price'      => $v['price'],
                'price_type' => 'fixed',
                'position'   => $pos,
                'en'         => ['label' => $v['name']],
                'ms'         => ['label' => $v['name']],
            ]);
        }
        echo "  Created {$pos} RoyalLift variants\n";
    } else {
        echo "  RoyalLift variants already exist\n";
    }

    // ─── AURACURVE ───────────────────────────────────────────
    $acVariants = [
        ['name' => 'AURA CURVE 100ML',              'price' => 967],
        ['name' => 'AURA CURVE 300ML',              'price' => 2903],
        ['name' => 'AURA CURVE 400ML',              'price' => 3508],
        ['name' => 'AURA CURVE 600ML',              'price' => 4839],
        ['name' => 'AURA CURVE 1000ML',             'price' => 7259],
        ['name' => 'AURA CURVE 2000ML',             'price' => 14035],
        ['name' => 'AURA LIPS',                      'price' => 1450],
        ['name' => 'AURA EYEBAG',                    'price' => 1450],
        ['name' => 'AURA MISS V',                    'price' => 3145],
        ['name' => 'AURA CHIN',                      'price' => 1209],
        ['name' => 'AURA SMILE LINE',                'price' => 1450],
        ['name' => 'AURA MUKA',                      'price' => 1330],
        ['name' => 'SERVICE AURA',                   'price' => 363],
        ['name' => 'CAIR/REMOVE KETUL AURA',         'price' => 778],
    ];

    $ac = Product::where('sku', 'AURA_CURVE_POSTER')->first();
    if (!$ac) {
        $ac = Product::create([
            'sku'            => 'AURA_CURVE_POSTER',
            'price'          => 363,
            'selling_price'  => 363,
            'manage_stock'   => 0,
            'qty'            => 0,
            'in_stock'       => 1,
            'is_active'      => 1,
            'is_virtual'     => 1,
            'en' => [
                'name'              => 'AuraCurve',
                'description'       => '<p><strong>AuraCurve – Senarai Rawatan Estetika Kontur Badan & Wajah (Imma Serilaris)</strong></p><p>Rawatan AuraCurve ialah rawatan estetika suntikan filler dan lipo berasaskan hyaluronic acid / lipolysis untuk membentuk kontur badan (lengan, perut, paha, punggung) dan wajah (bibir, dagu, eyebag, smile line) tanpa pembedahan.</p>',
                'short_description' => 'Senarai rawatan estetika AuraCurve termaju (Lipo Curve, Lips, Eyebag, Miss V, Chin, Smile Line, Muka).',
            ],
            'ms' => [
                'name'              => 'AuraCurve',
                'description'       => '<p><strong>AuraCurve – Senarai Rawatan Estetika Kontur Badan & Wajah (Imma Serilaris)</strong></p><p>Rawatan AuraCurve ialah rawatan estetika suntikan filler dan lipo berasaskan hyaluronic acid / lipolysis untuk membentuk kontur badan (lengan, perut, paha, punggung) dan wajah (bibir, dagu, eyebag, smile line) tanpa pembedahan.</p>',
                'short_description' => 'Senarai rawatan estetika AuraCurve termaju (Lipo Curve, Lips, Eyebag, Miss V, Chin, Smile Line, Muka).',
            ],
        ]);
        echo "Created AuraCurve product ID={$ac->id}\n";
    } else {
        echo "AuraCurve already exists ID={$ac->id}\n";
    }

    // Attach categories
    $catIds2 = array_filter([$aestheticCategory?->id, $lipoCategory?->id]);
    if (!empty($catIds2)) {
        $ac->categories()->syncWithoutDetaching($catIds2);
        echo "  Attached " . count($catIds2) . " categories\n";
    }

    // Create variation, option, and variants if none exist
    if ($ac->variants()->count() === 0) {
        $variation2 = Variation::create([
            'uid'       => Str::uuid()->toString(),
            'type'      => 'text',
            'is_global' => false,
            'position'  => 1,
            'en'        => ['name' => 'Pilih Rawatan / Varian AuraCurve'],
            'ms'        => ['name' => 'Pilih Rawatan / Varian AuraCurve'],
        ]);
        $ac->variations()->attach($variation2->id);

        $option2 = Option::create([
            'type'        => 'dropdown',
            'is_required' => true,
            'is_global'   => false,
            'position'    => 1,
            'en'          => ['name' => 'Pilih Rawatan / Varian AuraCurve'],
            'ms'          => ['name' => 'Pilih Rawatan / Varian AuraCurve'],
        ]);
        $ac->options()->attach($option2->id);

        $pos = 0;
        foreach ($acVariants as $v) {
            $pos++;
            $uid = Str::uuid()->toString();

            $variation2->values()->create([
                'uid'      => $uid,
                'value'    => '',
                'position' => $pos,
                'en'       => ['label' => $v['name']],
                'ms'       => ['label' => $v['name']],
            ]);

            $ac->variants()->create([
                'uid'           => $uid,
                'uids'          => $uid,
                'name'          => $v['name'],
                'sku'           => Str::slug($v['name'], '_'),
                'price'         => $v['price'],
                'selling_price' => $v['price'],
                'manage_stock'  => 0,
                'qty'           => 0,
                'in_stock'      => 1,
                'is_active'     => true,
                'is_default'    => ($pos === 1),
                'position'      => $pos,
            ]);

            $option2->values()->create([
                'price'      => $v['price'],
                'price_type' => 'fixed',
                'position'   => $pos,
                'en'         => ['label' => $v['name']],
                'ms'         => ['label' => $v['name']],
            ]);
        }
        echo "  Created {$pos} AuraCurve variants\n";
    } else {
        echo "  AuraCurve variants already exist\n";
    }

    echo "\n✅ DONE! RoyalLift & AuraCurve poster products seeded successfully.\n";
});
