<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Support\WhatsAppNotificationDefaults;

return new class extends Migration
{
    /** @var list<string> */
    private array $keys = [
        'whatsapp_beautician_tba_reminder_enabled',
        'whatsapp_beautician_tba_reminder_time',
        'whatsapp_beautician_tba_reminder_repeat_days',
        'whatsapp_beautician_tba_reminder_message',
    ];


    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $defaults = WhatsAppNotificationDefaults::all();

        foreach ($this->keys as $key) {
            if (! array_key_exists($key, $defaults) || Setting::has($key)) {
                continue;
            }

            Setting::set($key, $defaults[$key]);
        }
    }


    public function down(): void
    {
        // Preserve administrator notification preferences on rollback.
    }
};
