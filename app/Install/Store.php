<?php

namespace AestheticCart\Install;

use Modules\Setting\Entities\Setting;

class Store
{
    public function setup($request): void
    {
        Setting::setMany([
            'translatable' => [
                'store_name' => $request['store_name'],
            ],
            'store_email' => $request['store_email'],
            'store_phone' => $request['store_phone'],
            'search_engine' => 'mysql',
        ]);
    }
}
