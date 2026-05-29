<?php

return [

    'attributes' => [

        'attribute_set_id' => 'Atribut Set',
        'name' => 'Nama',
        'categories' => 'Kategori',
        'slug' => 'URL',
        'is_filterable' => 'Boleh ditapis',
    ],
    'attribute_sets' => [

        'name' => 'Nama',
    ],
    'product_attributes' => [

        'attributes.*.attribute_id' => 'Atribut',
        'attributes.*.values' => 'Nilai',
    ],
];
