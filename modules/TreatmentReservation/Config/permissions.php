<?php

return [
    'admin.treatment_reservations' => [
        'index' => 'treatmentreservation::permissions.index',
        'create' => 'treatmentreservation::permissions.create',
        'edit' => 'treatmentreservation::permissions.edit',
    ],
    'admin.treatment_reservations.portal' => [
        'create' => 'treatmentreservation::permissions.portal_create',
    ],
];
