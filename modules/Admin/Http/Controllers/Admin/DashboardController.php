<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Response;
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

        $treatmentQuery = Order::query()
            ->whereNotNull('beautician_id')
            ->withoutCanceledOrders();

        $loyaltyEnabled = Module::isEnabled('Loyalty');

        return view('admin::dashboard.index', [
            'totalSales' => Order::totalSales(),
            'totalOrders' => Order::withoutCanceledOrders()->count(),
            'totalProducts' => Product::withoutGlobalScope('active')->count(),
            'totalCustomers' => User::totalCustomers(),
            'treatmentSales' => Money::inDefaultCurrency((clone $treatmentQuery)->sum('total')),
            'treatmentOrders' => (clone $treatmentQuery)->count(),
            'todayAppointments' => Order::query()
                ->whereNotNull('appointment_date')
                ->withoutCanceledOrders()
                ->whereDate('appointment_date', today())
                ->count(),
            'showTreatmentStats' => Module::isEnabled('Beautician') && Module::isEnabled('BeauticianReport'),
            'beauticianAnalyticsUrl' => Module::isEnabled('BeauticianReport')
                ? route('admin.beautician_reports.index')
                : null,
            'showLoyaltyMembersCard' => $loyaltyEnabled,
            'loyaltyMembersUrl' => $loyaltyEnabled ? route('admin.loyalty.members.index') : null,
            'loyaltyMembersTotal' => $loyaltyEnabled ? LoyaltyWallet::count() : 0,
            'loyaltyMembersWithBalance' => $loyaltyEnabled
                ? LoyaltyWallet::where('balance', '>', 0)->count()
                : 0,
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
