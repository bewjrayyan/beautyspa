<?php

return [
    'admin.beauticians' => [
        'index' => 'beautician::permissions.index',
        'create' => 'beautician::permissions.create',
        'edit' => 'beautician::permissions.edit',
        'destroy' => 'beautician::permissions.destroy',
    ],
    'admin.beautician_job_titles' => [
        'index' => 'beautician::permissions.job_titles.index',
        'create' => 'beautician::permissions.job_titles.create',
        'edit' => 'beautician::permissions.job_titles.edit',
        'destroy' => 'beautician::permissions.job_titles.destroy',
    ],
];
