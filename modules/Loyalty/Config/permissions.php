<?php

return [
    'admin.loyalty.tiers' => [
        'index' => 'loyalty::permissions.tiers.index',
        'create' => 'loyalty::permissions.tiers.create',
        'edit' => 'loyalty::permissions.tiers.edit',
        'destroy' => 'loyalty::permissions.tiers.destroy',
    ],
    'admin.loyalty.members' => [
        'index' => 'loyalty::permissions.members.index',
        'show' => 'loyalty::permissions.members.show',
        'adjust' => 'loyalty::permissions.members.adjust',
    ],
    'admin.loyalty.reports' => [
        'index' => 'loyalty::permissions.reports.index',
    ],
];
