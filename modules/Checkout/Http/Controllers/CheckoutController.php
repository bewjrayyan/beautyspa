<?php

namespace Modules\Checkout\Http\Controllers;

use Exception;
use Modules\Order\Entities\Order;
use Modules\Support\Country;
use Modules\Cart\Facades\Cart;
use Modules\Page\Entities\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Modules\Payment\Facades\Gateway;
use Modules\Payment\Services\ChipPaymentMethodConfig;
use Modules\Payment\Services\ChipPaymentMethodsResolver;
use Modules\Payment\Services\PaymentGatewayResolver;
use Modules\Support\Money;
use Illuminate\Contracts\View\Factory;
use Modules\Coupon\Checkers\ValidCoupon;
use Modules\Coupon\Checkers\CouponExists;
use Modules\Coupon\Checkers\MinimumSpend;
use Modules\Coupon\Checkers\MaximumSpend;
use Modules\User\Services\CustomerService;
use Modules\Checkout\Services\CheckoutPaymentFinalizer;
use Modules\Checkout\Services\CheckoutCompletionGuard;
use Modules\Checkout\Services\OrderService;
use Modules\Coupon\Checkers\AlreadyApplied;
use Modules\Address\Entities\DefaultAddress;
use Modules\Checkout\Services\CheckoutBillingDefaults;
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
use Modules\TreatmentReservation\Entities\TreatmentBranchAvailability;
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
     * @param CheckoutPaymentFinalizer $paymentFinalizer
     *
     * @return JsonResponse
     */
    public function store(
        StoreOrderRequest $request,
        CustomerService $customerService,
        OrderService $orderService,
        CheckoutPaymentFinalizer $paymentFinalizer
    ) {
        if (auth()->guest() && $request->create_an_account) {
            $customerService->register($request)->login();
        }

        $order = $orderService->create($request);

        CheckoutCompletionGuard::rememberPendingOrder($order);

        $gateway = PaymentGatewayResolver::get($request->payment_method);

        if ($gateway === null) {
            $orderService->delete($order);

            return response()->json([
                'message' => trans('payment::messages.payment_gateway_error'),
            ], 403);
        }

        try {
            $response = $gateway->purchase($order, $request);
        } catch (\Throwable $e) {
            $orderService->delete($order);

            return response()->json([
                'message' => $e->getMessage() ?: trans('storefront::storefront.something_went_wrong'),
            ], 403);
        }

        if (CheckoutCompletionGuard::isOfflineMethod($request->payment_method)) {
            return $this->completeOfflineOrder(
                $order,
                $gateway,
                $request->payment_method,
                $response,
                $paymentFinalizer
            );
        }

        return response()->json($response);
    }


    private function completeOfflineOrder(
        $order,
        $gateway,
        string $paymentMethod,
        $purchaseResponse,
        CheckoutPaymentFinalizer $paymentFinalizer
    ): JsonResponse
    {
        try {
            $completionResponse = $gateway->complete($order);
            $paymentFinalizer->finalize($order, $paymentMethod, $completionResponse);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: trans('storefront::storefront.something_went_wrong'),
            ], 403);
        }

        return response()->json(array_merge($purchaseResponse->toArray(), [
            'orderId' => $order->id,
            'redirectUrl' => storefront_route('checkout.complete.show'),
        ]));
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
        $user = auth()->user();
        $treatmentProductId = $requiresTreatmentBooking
            ? $this->resolveCartTreatmentProductId()
            : null;

        if ($user) {
            $user->loadMissing(['defaultAddress', 'addresses']);
        }

        return [
            'customerEmail' => $user?->email,
            'customerPhone' => $user?->phone
                ? \Modules\User\Support\PhoneNumber::toE164($user->phone)
                : null,
            'customerBilling' => app(CheckoutBillingDefaults::class)->forUser($user),
            'addresses' => $this->getAddresses(),
            'defaultAddress' => $user?->defaultAddress ?? new DefaultAddress(),
            'gateways' => $this->checkoutGateways(),
            'countries' => Country::supported(),
            'requiresTreatmentBooking' => $requiresTreatmentBooking,
            'beauticians' => $requiresTreatmentBooking
                ? Beautician::activeListForCheckout()
                : [],
            'availabilitySlotsUrl' => $requiresTreatmentBooking && app('modules')->isEnabled('TreatmentReservation')
                ? route('treatment_reservations.availability.slots', ['beautician' => '__BEAUTICIAN__'])
                : null,
            'availabilityDatesUrl' => $requiresTreatmentBooking && app('modules')->isEnabled('TreatmentReservation')
                ? route('treatment_reservations.availability.dates')
                : null,
            'treatmentProductId' => $treatmentProductId,
            'treatmentAllowTbaByBranch' => $treatmentProductId && app('modules')->isEnabled('TreatmentReservation')
                ? TreatmentBranchAvailability::query()
                    ->where('product_id', $treatmentProductId)
                    ->pluck('allow_tba', 'spa_branch_id')
                    ->map(fn ($allowed) => (bool) $allowed)
                    ->all()
                : [],
            'slotLabels' => array_merge(
                [
                    'select_beautician' => trans('storefront::checkout.select_beautician'),
                    'select_spa_branch_first' => trans('storefront::checkout.select_spa_branch_first'),
                    'no_beauticians_at_branch' => trans('storefront::checkout.no_beauticians_at_branch'),
                ],
                $requiresTreatmentBooking && app('modules')->isEnabled('TreatmentReservation')
                    ? [
                        'loading' => trans('treatmentreservation::public.loading_slots'),
                        'empty' => trans('treatmentreservation::public.no_slots'),
                        'select' => trans('storefront::checkout.select_appointment_time'),
                    ]
                    : []
            ),
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
    private function checkoutGateways(): array
    {
        $resolver = app(ChipPaymentMethodsResolver::class);

        return Gateway::all()
            ->map(function ($gateway, string $name) use ($resolver) {
                $data = [
                    'id' => $name,
                    'label' => $gateway->label ?? '',
                    'description' => $gateway->description ?? '',
                    'instructions' => $gateway->instructions ?? null,
                ];

                if (ChipPaymentMethodConfig::isChipPaymentMethod($name)) {
                    $cartSubunit = Money::inCurrentCurrency(Cart::total()->amount())->subunit();
                    $data['surcharge_subunit'] = $resolver->surchargeSubunit($name, null, $cartSubunit);
                }

                return $data;
            })
            ->all();
    }


    private function getAddresses()
    {
        if (auth()->guest()) {
            return collect();
        }

        return auth()->user()->addresses->keyBy('id');
    }


    private function resolveCartTreatmentProductId(): ?int
    {
        foreach (Cart::items() as $item) {
            $product = $item->product ?? null;

            if ($product && ($product->is_virtual ?? false)) {
                return (int) $product->id;
            }
        }

        return null;
    }
}
