<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Setting\Support\WhatsAppNotificationDefaults;

return new class extends Migration
{
    public function up(): void
    {
        WhatsAppNotificationDefaults::applyMissingOnly();
    }

    public function down(): void
    {
        // Defaults are not reverted.
    }
};
