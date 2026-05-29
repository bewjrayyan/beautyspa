<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Loyalty\Support\LoyaltySettingsDefaults;

return new class extends Migration
{
    public function up(): void
    {
        LoyaltySettingsDefaults::applyMissingOnly();
    }

    public function down(): void
    {
        //
    }
};
