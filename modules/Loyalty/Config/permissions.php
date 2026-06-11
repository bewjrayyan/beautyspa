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
    'admin.loyalty.stamp_programs' => [
        'index' => 'loyalty::permissions.stamp_programs.index',
        'create' => 'loyalty::permissions.stamp_programs.create',
        'edit' => 'loyalty::permissions.stamp_programs.edit',
        'destroy' => 'loyalty::permissions.stamp_programs.destroy',
    ],
];
