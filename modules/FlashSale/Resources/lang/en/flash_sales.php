<?php

return [
    'flash_sale' => 'Flash Sale',
    'flash_sales' => 'Flash Sales',
    'table' => [
        'campaign_name' => 'Campaign Name',
    ],
    'tabs' => [
        'group' => [
            'flash_sale_information' => 'Flash Sale Information',
        ],
        'products' => 'Products',
        'settings' => 'Settings',
    ],
    'products_tab' => [
        'title' => 'Campaign treatments',
        'lead' => 'Add virtual treatments with flash pricing and an end date. Promotion slots are optional caps — not warehouse stock.',
        'stats' => [
            'treatments' => 'Treatments',
            'sold' => 'Slots sold',
            'unlimited' => 'Unlimited slots',
        ],
        'empty_title' => 'No treatments yet',
        'empty_text' => 'Search and add virtual treatments to run this flash sale campaign.',
        'field_treatment' => 'Treatment',
        'search_placeholder' => 'Type to search treatments…',
        'end_placeholder' => 'Campaign end date & time',
        'item_heading' => 'New treatment',
        'sold_label' => 'sold',
        'drag' => 'Drag to reorder',
        'remove' => 'Remove treatment',
        'flash_price' => 'Flash sale price',
        'catalog_price' => 'Catalog price',
        'flash_price_help' => 'Campaign price charged during this flash sale. It does not change the treatment’s regular catalog price.',
        'flash_price_options_note' => 'Product options (add-ons) are still added on top of this flash sale base price at checkout.',
    ],
    'form' => [
        'add_product' => 'Add Treatment',
        'flash_sale_product' => 'Flash Sale Treatment',
        'virtual_treatments_intro' => 'Treatments do not use warehouse stock. Only virtual/treatment products appear in search.',
        'promotion_slots' => 'Promotion slots',
        'qty_unlimited_virtual' => 'Set slots to 0 for unlimited availability during the campaign.',
    ],
    'validation' => [
        'qty_required_physical' => 'Physical products need at least 1 promotion slot.',
        'virtual_only' => 'Only virtual/treatment products can be added to this flash sale.',
    ],
];
