<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Database\Seeders\BeautyTransformationTreatmentSeeder;

(new BeautyTransformationTreatmentSeeder())->run();

echo "Beauty Transformation treatment product and 14 variants seeded successfully.\n";
