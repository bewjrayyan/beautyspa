<?php

return [
    'admin.settings' => [
        'edit' => 'setting::permissions.edit',
    ],
    'admin.operations' => [
        'view' => 'setting::permissions.operations.view',
        'manage_queue' => 'setting::permissions.operations.manage_queue',
        'manage_retention' => 'setting::permissions.operations.manage_retention',
    ],
];
