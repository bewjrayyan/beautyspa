<?php

return [
    /*
    | Shared secret for /catalog-sync/bundle (set the same token on local + production).
    | Local: export URL for production to pull. Production: validates incoming pulls.
    */
    'token' => env('CATALOG_SYNC_TOKEN'),

    /*
    | Default dev export URL (production pulls from here when using Admin → System).
    | localhost is only reachable from the same machine — use ngrok or upload the bundle for remote production.
    */
    'default_source_url' => env('CATALOG_SYNC_SOURCE_URL', 'http://localhost/fleetcart/catalog-sync/bundle'),

    'export_dir' => storage_path('app/demo-export'),
    'bundle_filename' => 'catalog-bundle.zip',
];
