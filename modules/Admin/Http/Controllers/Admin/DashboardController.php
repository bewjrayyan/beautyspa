<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Modules\User\Entities\User;
use Modules\Order\Entities\Order;
use Modules\Review\Entities\Review;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\SearchTerm;
use Illuminate\Database\Eloquent\Collection;
use Modules\Support\Money;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Nwidart\Modules\Facades\Module;

class DashboardController
{
    /**
     * Display the dashboard with its widgets.
     *
     * @return Response
     */
    public function index()
    {
        if (auth()->user()?->isBeauticianOnly()) {
            return redirect()->route('admin.treatment_reservations.portal');
        }

        $loyaltyEnabled = Module::isEnabled('Loyalty');
        $showTreatmentStats = Module::isEnabled('Beautician') && Module::isEnabled('BeauticianReport');
        $stats = $this->cachedDashboardStats($loyaltyEnabled, $showTreatmentStats);

        return view('admin::dashboard.index', [
            'totalSales' => $stats['totalSales'],
            'totalOrders' => $stats['totalOrders'],
            'totalProducts' => $stats['totalProducts'],
            'totalCustomers' => $stats['totalCustomers'],
            'treatmentSales' => $stats['treatmentSales'],
            'treatmentOrders' => $stats['treatmentOrders'],
            'todayAppointments' => $stats['todayAppointments'],
            'showTreatmentStats' => $showTreatmentStats,
            'beauticianAnalyticsUrl' => Module::isEnabled('BeauticianReport')
                ? route('admin.beautician_reports.index')
                : null,
            'showLoyaltyMembersCard' => $loyaltyEnabled,
            'loyaltyMembersUrl' => $loyaltyEnabled ? route('admin.loyalty.members.index') : null,
            'loyaltyMembersTotal' => $stats['loyaltyMembersTotal'],
            'loyaltyMembersWithBalance' => $stats['loyaltyMembersWithBalance'],
            'recentLoyaltyMembers' => $loyaltyEnabled
                ? LoyaltyWallet::query()
                    ->with(['user', 'tier'])
                    ->latest('updated_at')
                    ->take(5)
                    ->get()
                : collect(),
            'latestSearchTerms' => $this->getLatestSearchTerms(),
            'latestOrders' => $this->getLatestOrders(),
            'latestReviews' => $this->getLatestReviews(),
        ]);
    }


    /**
     * @return array<string, mixed>
     */
    private function cachedDashboardStats(bool $loyaltyEnabled, bool $showTreatmentStats): array
    {
        $cacheKey = 'admin.dashboard.stats.'.($loyaltyEnabled ? 'loyalty' : 'no-loyalty').'.'.($showTreatmentStats ? 'treatment' : 'no-treatment');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($loyaltyEnabled, $showTreatmentStats) {
            $treatmentQuery = Order::query()
                ->whereNotNull('beautician_id')
                ->withoutCanceledOrders();

            $loyaltyCounts = $loyaltyEnabled
                ? LoyaltyWallet::query()
                    ->selectRaw('COUNT(*) as total')
                    ->selectRaw('SUM(CASE WHEN balance > 0 THEN 1 ELSE 0 END) as with_balance')
                    ->first()
                : null;

            return [
                'totalSales' => Order::totalSales(),
                'totalOrders' => Order::withoutCanceledOrders()->count(),
                'totalProducts' => Product::withoutGlobalScope('active')->count(),
                'totalCustomers' => User::totalCustomers(),
                'treatmentSales' => $showTreatmentStats
                    ? Money::inDefaultCurrency((clone $treatmentQuery)->sum('total'))
                    : Money::inDefaultCurrency(0),
                'treatmentOrders' => $showTreatmentStats ? (clone $treatmentQuery)->count() : 0,
                'todayAppointments' => Order::query()
                    ->whereNotNull('appointment_date')
                    ->withoutCanceledOrders()
                    ->whereDate('appointment_date', today())
                    ->count(),
                'loyaltyMembersTotal' => $loyaltyEnabled ? (int) ($loyaltyCounts->total ?? 0) : 0,
                'loyaltyMembersWithBalance' => $loyaltyEnabled ? (int) ($loyaltyCounts->with_balance ?? 0) : 0,
            ];
        });
    }


    private function getLatestSearchTerms()
    {
        return SearchTerm::latest('updated_at')->take(5)->get();
    }


    /**
     * Get latest five orders.
     *
     * @return Collection
     */
    private function getLatestOrders()
    {
        return Order::select([
            'id',
            'customer_first_name',
            'customer_last_name',
            'total',
            'status',
            'created_at',
        ])->latest()->take(5)->get();
    }


    /**
     * Get latest five reviews.
     *
     * @return Collection
     */
    private function getLatestReviews()
    {
        return Review::select('id', 'product_id', 'reviewer_name', 'rating')
            ->has('product')
            ->with('product:id')
            ->limit(5)
            ->get();
    }
}
