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
                $paymentFinalizer,
                $orderService
            );
        }

        return response()->json($response);
    }


    private function completeOfflineOrder(
        $order,
        $gateway,
        string $paymentMethod,
        $purchaseResponse,
        CheckoutPaymentFinalizer $paymentFinalizer,
        OrderService $orderService,
    ): JsonResponse
    {
        // Explicit thank-you session after offline finalize (bank_transfer/cod).
        // User: order+appointment saved but thank-you page missing.
        try {
            $completionResponse = $gateway->complete($order);
            $paymentFinalizer->finalize($order, $paymentMethod, $completionResponse);
            $order = $order->fresh() ?? $order;
        } catch (\Throwable $e) {
            report($e);

            $fresh = $order->fresh(['treatmentBookings', 'products.product']);

            if (
                $fresh
                && (
                    $fresh->isPaymentPaid()
                    || $fresh->treatmentBookings()->exists()
                    || $fresh->products->contains(fn ($line) => (bool) ($line->product?->is_virtual))
                )
            ) {
                return response()->json([
                    'orderId' => $fresh->id,
                    'redirectUrl' => CheckoutCompletionGuard::thankYouUrl($fresh),
                ]);
            }

            try {
                $orderService->delete($fresh ?? $order);
            } catch (\Throwable $cleanupException) {
                report($cleanupException);
            }

            session()->forget('checkout_pending_order');

            return response()->json([
                'message' => $e->getMessage() ?: trans('storefront::storefront.something_went_wrong'),
            ], 403);
        }

        $payload = is_object($purchaseResponse) && method_exists($purchaseResponse, 'toArray')
            ? $purchaseResponse->toArray()
            : (array) $purchaseResponse;

        return response()->json(array_merge($payload, [
            'orderId' => $order->id,
            'redirectUrl' => CheckoutCompletionGuard::thankYouUrl($order),
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
        $treatmentCartItems = $requiresTreatmentBooking
            ? $this->resolveCartTreatmentItems()
            : [];
        $treatmentProductId = $treatmentCartItems[0]['product_id'] ?? null;

        if ($user) {
            $user->loadMissing(['defaultAddress', 'addresses']);
        }

        $allowTbaByProductBranch = [];
        $durationByProductBranch = [];
        if ($treatmentCartItems !== [] && app('modules')->isEnabled('TreatmentReservation')) {
            $productIds = collect($treatmentCartItems)->pluck('product_id')->unique()->all();
            $rows = TreatmentBranchAvailability::query()
                ->whereIn('product_id', $productIds)
                ->get(['product_id', 'spa_branch_id', 'allow_tba']);

            foreach ($rows as $row) {
                $allowTbaByProductBranch[(int) $row->product_id][(int) $row->spa_branch_id] = (bool) $row->allow_tba;
            }

            $availability = app(\Modules\TreatmentReservation\Services\AppointmentAvailabilityService::class);
            foreach ($productIds as $productId) {
                foreach (array_keys($allowTbaByProductBranch[(int) $productId] ?? []) as $branchId) {
                    $durationByProductBranch[(int) $productId][(int) $branchId] = $availability->resolveDurationMinutes(
                        (int) $productId,
                        (int) $branchId
                    );
                }
            }
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
            'treatmentCartItems' => $treatmentCartItems,
            'treatmentAllowTbaByProductBranch' => $allowTbaByProductBranch,
            'treatmentDurationByProductBranch' => $durationByProductBranch,
            'treatmentAllowTbaByBranch' => $treatmentProductId && isset($allowTbaByProductBranch[(int) $treatmentProductId])
                ? $allowTbaByProductBranch[(int) $treatmentProductId]
                : [],
            'slotLabels' => array_merge(
                [
                    'select_beautician' => trans('storefront::checkout.select_beautician'),
                    'select_beautician_before_date' => trans('storefront::checkout.select_beautician_before_date'),
                    'select_date' => trans('storefront::checkout.select_date_first'),
                    'select_spa_branch_first' => trans('storefront::checkout.select_spa_branch_first'),
                    'no_beauticians_at_branch' => trans('storefront::checkout.no_beauticians_at_branch'),
                ],
                $requiresTreatmentBooking && app('modules')->isEnabled('TreatmentReservation')
                    ? [
                        'loading' => trans('treatmentreservation::public.loading_slots'),
                        'empty' => trans('treatmentreservation::public.no_slots'),
                        'select' => trans('storefront::checkout.select_appointment_time'),
                        'booked' => trans('treatmentreservation::public.slot_booked'),
                        'unavailable' => trans('treatmentreservation::public.slot_status_unavailable'),
                        'dateFullyBooked' => trans('treatmentreservation::public.date_fully_booked'),
                        'dateClosed' => trans('treatmentreservation::public.date_closed'),
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
        return $this->resolveCartTreatmentItems()[0]['product_id'] ?? null;
    }


    /**
     * @return list<array{cart_item_id: string, product_id: int, name: string}>
     */
    private function resolveCartTreatmentItems(): array
    {
        $items = [];

        foreach (Cart::items() as $item) {
            $product = $item->product ?? null;

            if (! $product || ! $product->isVirtualTreatment()) {
                continue;
            }

            $items[] = [
                'cart_item_id' => (string) $item->id,
                'product_id' => (int) $product->id,
                'name' => (string) ($product->name ?? 'Treatment'),
            ];
        }

        return $items;
    }
}
