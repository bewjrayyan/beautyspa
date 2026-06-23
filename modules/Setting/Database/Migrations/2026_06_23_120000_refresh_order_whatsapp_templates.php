<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Setting\Entities\Setting;

return new class extends Migration
{
    private array $templateKeys = [
        'whatsapp_new_order_admin_message',
        'whatsapp_new_order_customer_message',
        'whatsapp_completed_group_message',
        'whatsapp_completed_beautician_message',
    ];

    public function up(): void
    {
        $defaults = config('setting.whatsapp_notifications', []);

        foreach ($this->templateKeys as $key) {
            if (! isset($defaults[$key]) || ! is_string($defaults[$key]) || trim($defaults[$key]) === '') {
                continue;
            }

            Setting::set($key, $defaults[$key]);
        }
    }

    public function down(): void
    {
        //
    }
};
