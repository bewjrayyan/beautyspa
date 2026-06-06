<?php

namespace Modules\Checkout\Http\Controllers;

use Exception;
use Modules\Support\Country;
use Modules\Cart\Facades\Cart;
use Modules\Page\Entities\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Modules\Payment\Facades\Gateway;
use Illuminate\Contracts\View\Factory;
use Modules\Coupon\Checkers\ValidCoupon;
use Modules\Coupon\Checkers\CouponExists;
use Modules\Coupon\Checkers\MinimumSpend;
use Modules\Coupon\Checkers\MaximumSpend;
use Modules\User\Services\CustomerService;
use Modules\Checkout\Services\CheckoutCompletionGuard;
use Modules\Checkout\Services\OrderService;
use Modules\Coupon\Checkers\AlreadyApplied;
use Modules\Account\Entities\DefaultAddress;
use Modules\Coupon\Checkers\ExcludedProducts;
use Modules\Coupon\Checkers\ApplicableProducts;
use Modules\Coupon\Checkers\ExcludedCategories;
use Illuminate\Contracts\Foundation\Application;
use Modules\Coupon\Checkers\UsageLimitPerCoupon;
use Modules\Coupon\Checkers\ApplicableCategories;
use Modules\Order\Http\Requests\StoreOrderRequest;
use Modules\Beautician\Entities\Beautician;
use Modules\Loyalty\Services\LoyaltyConfig;
use Modules\Loyalty\Services\LoyaltyWalletService;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\Coupon\Checkers\UsageLimitPerCustomer;
use Modules\Cart\Http\Middleware\CheckCartItemsStock;
use Modules\Cart\Http\Middleware\RedirectIfCartIsEmpty;

class CheckoutController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware([
            RedirectIfCartIsEmpty::class,
        ]);

        $this->middleware([
            CheckCartItemsStock::class,
        ])->only('store');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreOrderRequest $request
     * @param CustomerService $customerService
     * @param OrderService $orderService
     *
     * @return JsonResponse
     */
    public function store(StoreOrderRequest $request, CustomerService $customerService, OrderService $orderService)
    {
        if (auth()->guest() && $request->create_an_account) {
            $customerService->register($request)->login();
        }

        $order = $orderService->create($request);

        CheckoutCompletionGuard::rememberPendingOrder($order);

        $gateway = Gateway::get($request->payment_method);

        try {
            $response = $gateway->purchase($order, $request);
        } catch (Exception $e) {
            $orderService->delete($order);

            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        return response()->json($response);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(): View|Factory|Application
    {
        Cart::clearCartConditions();

        $requiresTreatmentBooking = Cart::hasVirtualTreatment();

        $loyaltyBalance = 0;
        $loyaltyWorthRm = 0;
        if (auth()->check() && app('modules')->isEnabled('Loyalty')) {
            $wallet = app(LoyaltyWalletService::class)->getOrCreateForUser(auth()->user());
            $loyaltyBalance = $wallet->balance;
            $loyaltyWorthRm = app(LoyaltyConfig::class)->pointsToRm($wallet->balance);
        }

        return view('storefront::public.checkout.create', [
            'cart' => Cart::instance(),
            'termsPageURL' => Page::urlForPage(setting('storefront_terms_page')),
            'loyaltyBalance' => $loyaltyBalance,
            'loyaltyWorthRm' => $loyaltyWorthRm,
            'checkoutConfig' => $this->checkoutConfig(
                $requiresTreatmentBooking,
                $loyaltyBalance,
                $loyaltyWorthRm
            ),
        ]);
    }


    private function checkoutConfig(
        bool $requiresTreatmentBooking,
        int $loyaltyBalance,
        float $loyaltyWorthRm,
    ): array {
        return [
            'customerEmail' => auth()->user()?->email,
            'customerPhone' => auth()->user()?->phone,
            'addresses' => $this->getAddresses(),
            'defaultAddress' => auth()->user()?->defaultAddress ?? new DefaultAddress(),
            'gateways' => Gateway::all(),
            'countries' => Country::supported(),
            'requiresTreatmentBooking' => $requiresTreatmentBooking,
            'beauticians' => $requiresTreatmentBooking
                ? Beautician::activeListForCheckout()
                : [],
            'availabilitySlotsUrl' => $requiresTreatmentBooking && app('modules')->isEnabled('TreatmentReservation')
                ? route('treatment_reservations.availability.slots', ['beautician' => '__BEAUTICIAN__'])
                : null,
            'slotLabels' => $requiresTreatmentBooking && app('modules')->isEnabled('TreatmentReservation')
                ? [
                    'loading' => trans('treatmentreservation::public.loading_slots'),
                    'empty' => trans('treatmentreservation::public.no_slots'),
                    'select' => trans('storefront::checkout.select_appointment_time'),
                ]
                : [],
            'spaBranches' => app('modules')->isEnabled('SpaBranch')
                ? SpaBranch::activeListForCheckout()
                : [],
            'loyaltyBalance' => $loyaltyBalance,
            'loyaltyWorthRm' => $loyaltyWorthRm,
            'loyaltyMaxPoints' => 0,
        ];
    }


    /**
     * Get addresses for the logged in user.
     *
     * @return Collection
     */
    private function getAddresses()
    {
        if (auth()->guest()) {
            return collect();
        }

        return auth()->user()->addresses->keyBy('id');
    }
}
