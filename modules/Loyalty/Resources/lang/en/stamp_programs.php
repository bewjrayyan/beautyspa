<?php

return [
    'programs' => 'Stamp card programs',
    'program' => 'stamp card program',

    'index' => [
        'lead' => 'Configure visit-based stamp cards shown to customers after checkout (e.g. collect 7 treatments, get 1 free).',
        'stats_total' => 'Programs',
        'stats_active' => 'Active',
        'stats_active_cards' => 'Active customer cards',
        'members' => 'Customer cards',
        'validity_days_value' => ':days days',
        'empty' => 'No stamp card programs yet. Create one to start rewarding repeat visits.',
    ],

    'form' => [
        'name' => 'Program name',
        'reward_description' => 'Reward description',
        'reward_description_help' => 'Shown on the customer receipt, e.g. "7 visits — 1 free basic wash".',
        'stamps_required' => 'Stamps required',
        'validity_days' => 'Card validity (days)',
        'validity_days_help' => 'Number of days from the first stamp until the card expires.',
        'eligibility' => 'Eligibility',
        'virtual_treatments_only' => 'Only count virtual treatment bookings in the order',
        'product_ids' => 'Specific product IDs (optional)',
        'product_ids_help' => 'Comma-separated product IDs. When set, only these products earn a stamp (overrides treatment-only rule).',
        'sort_order' => 'Sort order',
    ],
];
