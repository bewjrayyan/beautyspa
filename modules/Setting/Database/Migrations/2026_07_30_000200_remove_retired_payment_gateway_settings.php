<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private const RETIRED_GATEWAYS = [
        'authorizenet',
        'bkash',
        'check_payment',
        'flutterwave',
        'instamojo',
        'iyzico',
        'nagad',
        'payfast',
        'paystack',
        'paytm',
        'razorpay',
        'sslcommerz',
        'stripe',
    ];

    public function up(): void
    {
        $settingIds = DB::table('settings')
            ->select(['id', 'key'])
            ->get()
            ->filter(fn (object $setting): bool => $this->isRetiredGatewayKey((string) $setting->key))
            ->pluck('id')
            ->all();

        if ($settingIds !== []) {
            DB::table('settings')->whereIn('id', $settingIds)->delete();
        }
    }

    public function down(): void
    {
        // Retired credentials are intentionally not restored.
    }

    private function isRetiredGatewayKey(string $key): bool
    {
        foreach (self::RETIRED_GATEWAYS as $gateway) {
            if (str_starts_with($key, "{$gateway}_")) {
                return true;
            }
        }

        return false;
    }
};
