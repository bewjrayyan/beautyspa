<?php

use Illuminate\Database\Migrations\Migration;
use Modules\WhatsappBirthdayReminder\Support\BirthdayReminderSettingsDefaults;

return new class extends Migration
{
    public function up(): void
    {
        BirthdayReminderSettingsDefaults::applyMissingOnly();
    }


    public function down(): void
    {
        //
    }
};
