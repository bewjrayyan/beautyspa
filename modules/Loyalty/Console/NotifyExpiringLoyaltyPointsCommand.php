<?php

namespace Modules\Loyalty\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Modules\Loyalty\Enums\TransactionType;
use Modules\Loyalty\Services\LoyaltyConfig;
use Modules\Loyalty\Entities\LoyaltyTransaction;
use Modules\Loyalty\Services\LoyaltyNotificationService;

class NotifyExpiringLoyaltyPointsCommand extends Command
{
    protected $signature = 'loyalty:notify-expiring {--days=}';

    protected $description = 'Notify customers about loyalty points expiring soon via WhatsApp.';


    public function handle(
        LoyaltyConfig $config,
        LoyaltyNotificationService $notifications
    ): int {
        $days = (int) ($this->option('days') ?: $config->expiringNotifyDays());
        $notified = 0;

        $transactions = LoyaltyTransaction::query()
            ->where('type', TransactionType::EARN)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($days)])
            ->with('wallet.user')
            ->get();

        foreach ($transactions->groupBy('wallet_id') as $earns) {
            $wallet = $earns->first()->wallet;
            $user = $wallet?->user;

            if (!$user || !$user->phone) {
                continue;
            }

            $points = 0;
            $earliestExpiry = null;

            foreach ($earns as $earn) {
                $cacheKey = 'loyalty_expiry_notice:' . $earn->id;

                if (Cache::has($cacheKey)) {
                    continue;
                }

                $points += $earn->points;
                $earliestExpiry = $earliestExpiry
                    ? min($earliestExpiry, $earn->expires_at)
                    : $earn->expires_at;

                Cache::put($cacheKey, true, $earn->expires_at);
            }

            if ($points <= 0 || !$earliestExpiry) {
                continue;
            }

            $daysLeft = max(1, (int) now()->diffInDays($earliestExpiry, false));
            $notifications->notifyPointsExpiring($user, $points, $daysLeft);
            $notified++;
        }

        $this->info("Sent {$notified} expiring-point notification(s).");

        return self::SUCCESS;
    }
}
