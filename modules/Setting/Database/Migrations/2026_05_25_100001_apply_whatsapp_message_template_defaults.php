<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Setting\Support\WhatsAppNotificationDefaults;

return new class extends Migration
{
  private array $messageKeys = [
        'whatsapp_customer_completed_message',
        'whatsapp_customer_followup_message',
    ];

    public function up(): void
    {
        $defaults = WhatsAppNotificationDefaults::all();

        foreach ($this->messageKeys as $key) {
            if (! isset($defaults[$key])) {
                continue;
            }

            $value = $defaults[$key];

            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            if (! \Modules\Setting\Entities\Setting::has($key)) {
                \Modules\Setting\Entities\Setting::set($key, $value);

                continue;
            }

            $current = \Modules\Setting\Entities\Setting::get($key);

            if ($current === null || (is_string($current) && trim($current) === '')) {
                \Modules\Setting\Entities\Setting::set($key, $value);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
