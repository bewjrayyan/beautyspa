<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Setting\Entities\Setting;

return new class extends Migration
{
    /**
     * Payment gateways removed from admin settings and checkout registration.
     */
    private array $disabledGatewayFlags = [
        'paytm_enabled',
        'razorpay_enabled',
        'instamojo_enabled',
        'paystack_enabled',
        'mercadopago_enabled',
        'payfast_enabled',
        'iyzico_enabled',
        'bkash_enabled',
        'nagad_enabled',
        'sslcommerz_enabled',
    ];

    public function up(): void
    {
        $settings = [];

        foreach ($this->disabledGatewayFlags as $key) {
            if (Setting::has($key)) {
                $settings[$key] = false;
            }
        }

        if ($settings !== []) {
            Setting::setMany($settings);
        }
    }

    public function down(): void
    {
        // Intentionally empty — previous enabled state is not restored.
    }
};
