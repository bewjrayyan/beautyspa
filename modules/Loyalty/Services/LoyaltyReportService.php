<?php

namespace Modules\Loyalty\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Loyalty\Enums\TransactionType;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Loyalty\Entities\LoyaltyTransaction;

class LoyaltyReportService
{
    public function __construct(private LoyaltyConfig $config) {}


    public function overview(?string $from = null, ?string $to = null): array
    {
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : now()->subDays(30)->startOfDay();
        $toDate = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();

        $outstandingPoints = (int) LoyaltyWallet::sum('balance');
        $activeMembers = LoyaltyWallet::where('balance', '>', 0)->count();
        $totalMembers = LoyaltyWallet::count();

        $txQuery = LoyaltyTransaction::query()
            ->whereBetween('created_at', [$fromDate, $toDate]);

        $earned = (int) (clone $txQuery)->where('type', TransactionType::EARN)->sum('points');
        $redeemed = (int) (clone $txQuery)->where('type', TransactionType::REDEEM)->sum(DB::raw('ABS(points)'));
        $expired = (int) (clone $txQuery)->where('type', TransactionType::EXPIRE)->sum(DB::raw('ABS(points)'));
        $bonuses = (int) (clone $txQuery)->where('type', TransactionType::BONUS)->sum('points');

        $ordersRedeemed = DB::table('orders')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->where('loyalty_points_redeemed', '>', 0)
            ->selectRaw('COUNT(*) as count, SUM(loyalty_discount_amount) as discount_rm, SUM(loyalty_points_redeemed) as points')
            ->first();

        $ordersEarned = DB::table('orders')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->where('loyalty_points_earned', '>', 0)
            ->selectRaw('COUNT(*) as count, SUM(loyalty_points_earned) as points')
            ->first();

        $tierBreakdown = LoyaltyWallet::query()
            ->select('loyalty_tiers.name', DB::raw('COUNT(*) as members'))
            ->join('loyalty_tiers', 'loyalty_tiers.id', '=', 'loyalty_wallets.tier_id')
            ->groupBy('loyalty_tiers.id', 'loyalty_tiers.name', 'loyalty_tiers.sort_order')
            ->orderBy('loyalty_tiers.sort_order')
            ->get();

        $expiringSoon = LoyaltyTransaction::query()
            ->where('type', TransactionType::EARN)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($this->config->expiringNotifyDays())])
            ->sum('points');

        return [
            'from' => $fromDate,
            'to' => $toDate,
            'outstanding_points' => $outstandingPoints,
            'liability_rm' => $this->config->pointsToRm($outstandingPoints),
            'active_members' => $activeMembers,
            'total_members' => $totalMembers,
            'earned_points' => $earned,
            'redeemed_points' => $redeemed,
            'expired_points' => $expired,
            'bonus_points' => $bonuses,
            'orders_with_redeem' => (int) ($ordersRedeemed->count ?? 0),
            'redeem_discount_rm' => (float) ($ordersRedeemed->discount_rm ?? 0),
            'orders_with_earn' => (int) ($ordersEarned->count ?? 0),
            'order_earn_points' => (int) ($ordersEarned->points ?? 0),
            'expiring_soon_points' => (int) $expiringSoon,
            'expiring_soon_rm' => $this->config->pointsToRm((int) $expiringSoon),
            'tier_breakdown' => $tierBreakdown,
            'point_value_rm' => $this->config->pointValueRm(),
        ];
    }


    public function recentTransactions(int $limit = 50)
    {
        return LoyaltyTransaction::query()
            ->with(['wallet.user', 'wallet.tier'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
