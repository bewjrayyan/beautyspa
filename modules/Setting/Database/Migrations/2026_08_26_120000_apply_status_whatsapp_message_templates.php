<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Support\WhatsAppNotificationDefaults;

return new class extends Migration
{
    /** @var list<string> */
    private array $templateKeys = [
        'whatsapp_order_status_message',
        'whatsapp_status_beautician_message',
    ];

    /** @var list<string> */
    private array $toggleKeys = [
        'whatsapp_status_notify_customer_enabled',
        'whatsapp_status_notify_beautician_enabled',
    ];

    public function up(): void
    {
        $defaults = WhatsAppNotificationDefaults::all();

        foreach ($this->templateKeys as $key) {
            if (! isset($defaults[$key]) || ! is_string($defaults[$key]) || trim($defaults[$key]) === '') {
                continue;
            }

            if (! Setting::has($key)) {
                Setting::set($key, $defaults[$key]);

                continue;
            }

            $current = Setting::get($key);

            if ($current === null || (is_string($current) && trim($current) === '')) {
                Setting::set($key, $defaults[$key]);
            }
        }

        foreach ($this->toggleKeys as $key) {
            if (! array_key_exists($key, $defaults)) {
                continue;
            }

            if (! Setting::has($key) || Setting::get($key) === null) {
                Setting::set($key, (bool) $defaults[$key]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
