<?php

return [

    'name' => 'Nama',
    'slug' => 'URL',
    'description' => 'Penerangan',
    'short_description' => 'Short Penerangan',
    'brand_id' => 'Jenama',
    'categories' => 'Kategori',
    'tax_class_id' => 'Cukai Class',
    'tags' => 'Tag',
    'is_virtual' => 'Virtual/Rawatan',
    'treatment_category_id' => 'Kategori Rawatan (Warna)',
    'is_active' => 'Status',
    'loyalty_bonus_points' => 'Mata bonus kesetiaan setiap unit',
    'loyalty_earn_multiplier' => 'Pengganda mata ganjaran',
    'price' => 'Harga',
    'special_price' => 'Special Harga',
    'special_price_type' => 'Special Harga Type',
    'special_price_start' => 'Special Harga Start',
    'special_price_end' => 'Special Harga End',
    'sku' => 'SKU',
    'manage_stock' => 'Pengurusan Inventori',
    'qty' => 'Qty',
    'in_stock' => 'Stok Availability',
    'new_from' => 'From Baharu',
    'new_to' => 'To Baharu',
    'up_sells' => 'Up-Sells',
    'cross_sells' => 'Cross-Sells',
    'related_products' => 'Produk Berkaitan',
    'attributes' => [

        '*.attribute_id' => 'Atribut',
        '*.values' => 'Nilai',
    ],
    'options' => [

        '*.name' => 'Nama',
        '*.type' => 'Jenis',
        '*.values.*.label' => 'Label',
        '*.values.*.price' => 'Harga',
        '*.values.*.price_type' => 'Harga Type',
    ],
    'variations' => [

        '*.name' => 'Nama',
        '*.type' => 'Jenis',
        '*.values' => 'Nilai',
        '*.values.*.label' => 'Label',
        '*.values.*.color' => 'Warna',
        '*.values.*.image' => 'Imej',
    ],
    'variants' => [

        '*.name' => 'Nama',
        '*.sku' => 'SKU',
        '*.is_active' => 'Status',
        '*.is_default' => 'Lalai',
        '*.price' => 'Harga',
        '*.special_price' => 'Special Harga',
        '*.special_price_type' => 'Special Harga Type',
        '*.special_price_start' => 'Special Harga Start',
        '*.special_price_end' => 'Special Harga End',
        '*.manage_stock' => 'Pengurusan Inventori',
        '*.qty' => 'Kuantiti',
        '*.in_stock' => 'Stok Availability',
    ],
];
