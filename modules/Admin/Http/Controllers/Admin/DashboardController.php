<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Order\Entities\Order;
use Modules\Product\Entities\SearchTerm;
use Modules\Review\Entities\Review;
use Modules\Support\Money;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;
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
            return redirect()->to(auth()->user()->adminHomeRoute());
        }

        $loyaltyEnabled = Module::isEnabled('Loyalty');
        $showAppointmentPanels = Module::isEnabled('Beautician');
        $topStats = $this->cachedTopStats($loyaltyEnabled);

        return view('admin::dashboard.index', [
            'totalSales' => $topStats['totalSales'],
            'thisMonthSales' => $topStats['thisMonthSales'],
            'pendingPaymentCount' => $topStats['pendingPaymentCount'],
            'todayAppointmentsCount' => $topStats['todayAppointmentsCount'],
            'totalCustomers' => $topStats['totalCustomers'],
            'loyaltyMembersTotal' => $topStats['loyaltyMembersTotal'],
            'loyaltyMembersWithBalance' => $topStats['loyaltyMembersWithBalance'],
            'topStatsShowLoyalty' => $loyaltyEnabled
                && auth()->user()?->hasAccess('admin.loyalty.members.index'),
            'showAppointmentPanels' => $showAppointmentPanels,
            'beauticianAnalyticsUrl' => Module::isEnabled('BeauticianReport')
                ? route('admin.beautician_reports.index')
                : null,
            'showLoyaltyMembersCard' => $loyaltyEnabled,
            'loyaltyMembersUrl' => $loyaltyEnabled ? route('admin.loyalty.members.index') : null,
            'recentLoyaltyMembers' => $loyaltyEnabled
                ? LoyaltyWallet::query()
                    ->with(['user', 'tier'])
                    ->latest('updated_at')
                    ->take(5)
                    ->get()
                : collect(),
            'todayAppointmentsList' => $showAppointmentPanels
                ? $this->getTodayAppointments()
                : collect(),
            'upcomingAppointments' => $showAppointmentPanels
                ? $this->getUpcomingAppointments()
                : collect(),
            'pendingOrders' => $this->getPendingOrders(),
            'latestSearchTerms' => $this->getLatestSearchTerms(),
            'latestOrders' => $this->getLatestOrders(),
            'latestReviews' => $this->getLatestReviews(),
            'recentCustomers' => $this->getRecentCustomers(),
        ]);
    }


    /**
     * @return array<string, mixed>
     */
    private function cachedTopStats(bool $loyaltyEnabled): array
    {
        $cacheKey = 'admin.dashboard.top_stats.'.($loyaltyEnabled ? 'loyalty' : 'no-loyalty');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($loyaltyEnabled) {
            $orderQuery = Order::query()->withoutCanceledOrders();

            $loyaltyCounts = $loyaltyEnabled
                ? LoyaltyWallet::query()
                    ->selectRaw('COUNT(*) as total')
                    ->selectRaw('SUM(CASE WHEN balance > 0 THEN 1 ELSE 0 END) as with_balance')
                    ->first()
                : null;

            return [
                'totalSales' => Order::totalSales(),
                'thisMonthSales' => Money::inDefaultCurrency(
                    (clone $orderQuery)
                        ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                        ->sum('total')
                ),
                'pendingPaymentCount' => (clone $orderQuery)->whereIn('payment_status', [
                    Order::PAYMENT_PENDING,
                    Order::PAYMENT_PROCESSING,
                ])->count(),
                'todayAppointmentsCount' => Order::query()
                    ->whereNotNull('appointment_date')
                    ->withoutCanceledOrders()
                    ->whereDate('appointment_date', today())
                    ->count(),
                'totalCustomers' => User::totalCustomers(),
                'loyaltyMembersTotal' => $loyaltyEnabled ? (int) ($loyaltyCounts->total ?? 0) : 0,
                'loyaltyMembersWithBalance' => $loyaltyEnabled ? (int) ($loyaltyCounts->with_balance ?? 0) : 0,
            ];
        });
    }


    private function getTodayAppointments()
    {
        return Order::select([
            'id',
            'customer_first_name',
            'customer_last_name',
            'total',
            'status',
            'appointment_date',
            'created_at',
        ])
            ->whereNotNull('appointment_date')
            ->withoutCanceledOrders()
            ->whereDate('appointment_date', today())
            ->orderBy('appointment_date')
            ->take(5)
            ->get();
    }


    private function getUpcomingAppointments()
    {
        return Order::select([
            'id',
            'customer_first_name',
            'customer_last_name',
            'total',
            'status',
            'appointment_date',
            'created_at',
        ])
            ->whereNotNull('appointment_date')
            ->withoutCanceledOrders()
            ->whereDate('appointment_date', '>', today())
            ->whereNotIn('status', [Order::CANCELED, Order::REFUNDED, Order::COMPLETED])
            ->orderBy('appointment_date')
            ->take(5)
            ->get();
    }


    private function getPendingOrders()
    {
        return Order::select([
            'id',
            'customer_first_name',
            'customer_last_name',
            'total',
            'status',
            'payment_status',
            'created_at',
        ])
            ->withoutCanceledOrders()
            ->whereIn('payment_status', [
                Order::PAYMENT_PENDING,
                Order::PAYMENT_PROCESSING,
            ])
            ->latest()
            ->take(5)
            ->get();
    }


    private function getLatestSearchTerms()
    {
        return SearchTerm::latest('updated_at')->take(5)->get();
    }


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


    private function getLatestReviews()
    {
        return Review::select('id', 'product_id', 'reviewer_name', 'rating')
            ->has('product')
            ->with('product:id')
            ->latest()
            ->limit(5)
            ->get();
    }


    private function getRecentCustomers()
    {
        return Role::findOrNew(setting('customer_role'))
            ->users()
            ->select([
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.phone',
                'users.created_at',
            ])
            ->orderByDesc('users.created_at')
            ->take(5)
            ->get();
    }
}
