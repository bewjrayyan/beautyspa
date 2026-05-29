<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Loyalty\Support\LoyaltySettingsDefaults;
use Modules\Setting\Entities\Setting;

return new class extends Migration
{
    public function up(): void
    {
        Setting::setMany(LoyaltySettingsDefaults::all());
    }

    public function down(): void
    {
        //
    }
};
