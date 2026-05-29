<?php

namespace Modules\Loyalty\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Loyalty\Services\LoyaltyConfig;
use Modules\Loyalty\Services\LoyaltyLifetimeSpendService;
use Modules\Loyalty\Services\LoyaltyTierService;
use Modules\Loyalty\Services\MemberPurchaseAnalyticsService;
use Modules\Loyalty\Services\LoyaltyWalletService;
use Modules\Loyalty\Http\Requests\AdjustMemberPointsRequest;
use Modules\Loyalty\Support\MemberUserSearch;
use Modules\Order\Entities\Order;

class MemberController
{
    public function __construct(
        private LoyaltyWalletService $wallets,
        private LoyaltyLifetimeSpendService $lifetimeSpend,
        private LoyaltyTierService $tiers,
        private MemberPurchaseAnalyticsService $purchaseAnalytics,
    ) {}


    public function index(Request $request)
    {
        if ($request->has('query')) {
            return LoyaltyWallet::with('user')
                ->whereHas('user', function ($q) use ($request) {
                    MemberUserSearch::apply($q, (string) $request->get('query'));
                })
                ->limit($request->get('limit', 10))
                ->get();
        }

        $outstandingPoints = (int) LoyaltyWallet::sum('balance');

        return view('loyalty::admin.members.index', [
            'stats' => [
                'total' => LoyaltyWallet::count(),
                'active' => LoyaltyWallet::where('balance', '>', 0)->count(),
                'outstanding_points' => $outstandingPoints,
                'liability_rm' => app(LoyaltyConfig::class)->pointsToRm($outstandingPoints),
                'total_spend' => (float) LoyaltyWallet::sum('lifetime_spend'),
            ],
            'tier_breakdown' => LoyaltyWallet::query()
                ->select('loyalty_tiers.name', DB::raw('COUNT(*) as members'))
                ->join('loyalty_tiers', 'loyalty_tiers.id', '=', 'loyalty_wallets.tier_id')
                ->groupBy('loyalty_tiers.id', 'loyalty_tiers.name', 'loyalty_tiers.sort_order')
                ->orderBy('loyalty_tiers.sort_order')
                ->get(),
        ]);
    }


    public function table(Request $request)
    {
        return (new LoyaltyWallet())->table($request);
    }


    public function show(LoyaltyWallet $wallet)
    {
        $wallet->load(['user.files', 'tier', 'transactions']);

        $this->lifetimeSpend->recalculateWallet($wallet);
        $this->tiers->evaluate($wallet->fresh(), 'member_view');
        $wallet->refresh();

        $transactions = $wallet->transactions;
        $user = $wallet->user;

        $memberOrders = collect();

        if ($user) {
            $memberOrders = Order::query()
                ->where('customer_id', $user->id)
                ->with(['products.product', 'products.product_variant', 'beautician'])
                ->latest()
                ->limit(25)
                ->get();
        }

        return view('loyalty::admin.members.show', [
            'member' => $wallet,
            'memberOrders' => $memberOrders,
            'purchaseAnalytics' => $this->purchaseAnalytics->forCustomer($user),
            'stats' => [
                'earned' => (int) $transactions->where('points', '>', 0)->sum('points'),
                'redeemed' => (int) abs($transactions->where('points', '<', 0)->sum('points')),
                'count' => $transactions->count(),
                'orders' => $memberOrders->count(),
            ],
        ]);
    }


    public function adjust(AdjustMemberPointsRequest $request, LoyaltyWallet $wallet): RedirectResponse
    {
        $this->wallets->adjust(
            $wallet,
            (int) $request->points,
            'adjust:' . now()->timestamp,
            $request->description,
            ['admin_user_id' => auth()->id()]
        );

        return back()->withSuccess(trans('loyalty::messages.points_adjusted'));
    }
}
