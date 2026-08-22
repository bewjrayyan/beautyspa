<?php

namespace Modules\Checkout\Services;

use Modules\Cart\CartTax;
use Modules\Cart\CartItem;
use Modules\Cart\Facades\Cart;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderCreated;
use Modules\Address\Entities\Address;
use Modules\FlashSale\Entities\FlashSale;
use Modules\Currency\Entities\CurrencyRate;
use Modules\Account\Entities\DefaultAddress;
use Modules\Shipping\Facades\ShippingMethod;
use Modules\Beautician\Entities\Beautician;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Exceptions\CheckoutException;
use Modules\Loyalty\Services\LoyaltyOrderService;
use Modules\Payment\Services\ChipPaymentMethodConfig;
use Modules\Payment\Services\ChipPaymentMethodsResolver;
use Modules\Support\Money;
use Modules\TreatmentReservation\Services\AppointmentAvailabilityService;
use Modules\TreatmentReservation\Services\CheckoutTreatmentScheduleHolds;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;
use Modules\TreatmentReservation\Support\AppointmentTimeFormatter;
use Modules\User\Support\PhoneNumber;

class OrderService
{
    public function create($request)
    {
        $this->mergeShippingAddress($request);
        $this->addShippingMethodToCart($request);

        return DB::transaction(function () use ($request) {
            $this->saveAddress($request);

            $lines = $this->treatmentBookingLines($request);
            \Modules\TreatmentReservation\Services\BookingSyncService::$pendingCheckoutLines = $lines;

            $order = $this->persistOrder($request, $lines);

            $this->storeOrderProducts($order);
            $this->storeOrderDownloads($order);
            $this->storeFlashSaleProductOrders($order);
            $this->incrementCouponUsage($order);
            $this->attachTaxes($order);
            $this->reduceStock();

            $order = $order->fresh(['products.product', 'products.variations', 'products.options.values', 'taxes']);

            if ($lines !== [] && app('modules')->isEnabled('TreatmentReservation')) {
                $sync = app(\Modules\TreatmentReservation\Services\BookingSyncService::class);

                if (\Modules\TreatmentReservation\Services\BookingSyncService::shouldDeferUntilPayment(
                    (string) $request->payment_method
                )) {
                    \Modules\TreatmentReservation\Services\BookingSyncService::storePendingCheckoutLines(
                        (int) $order->id,
                        $lines
                    );
                    app(\Modules\TreatmentReservation\Services\CheckoutSlotHoldService::class)
                        ->placeHoldsForOrder($order, $lines);
                    $order->forceFill(['checkout_treatment_lines' => $lines])->saveQuietly();
                } else {
                    $sync->syncCheckoutLines($order, $lines);
                }

                \Modules\TreatmentReservation\Services\BookingSyncService::$pendingCheckoutLines = null;
            }

            DB::afterCommit(function () use ($order) {
                event(new OrderCreated($order));
            });

            return $order;
        });
    }


    public function reduceStock()
    {
        Cart::reduceStock();
    }


    public function delete(Order $order)
    {
        \Modules\TreatmentReservation\Services\BookingSyncService::forgetPendingCheckoutLines((int) $order->id);
        app(\Modules\TreatmentReservation\Services\CheckoutSlotHoldService::class)
            ->releaseHoldsForOrder((int) $order->id);
        $this->refundLoyaltyRedemption($order);

        $order->delete();

        Cart::restoreStock();
    }


    private function mergeShippingAddress($request)
    {
        $request->merge([
            'shipping' => $request->ship_to_a_different_address ? $request->shipping : $request->billing,
        ]);
    }


    private function saveAddress($request)
    {
        if (auth()->guest()) {
            return;
        }

        $shouldSaveBilling = $request->boolean('save_billing_address');
        $shouldSaveShipping = $request->boolean('ship_to_a_different_address')
            && $request->boolean('save_shipping_address');

        if (! $shouldSaveBilling && ! $shouldSaveShipping) {
            return;
        }

        DB::table('users')
            ->where('id', auth()->id())
            ->lockForUpdate()
            ->value('id');

        if ($shouldSaveBilling) {
            $address = $this->storeUniqueAddress($request->billing);

            $this->makeDefaultAddress(
                $address,
                $request->boolean('make_billing_address_default')
            );
        }

        if ($shouldSaveShipping) {
            $address = $this->storeUniqueAddress($request->shipping);

            $this->makeDefaultAddress(
                $address,
                $request->boolean('make_shipping_address_default')
            );
        }
    }


    private function storeUniqueAddress($data): Address
    {
        return auth()
            ->user()
            ->addresses()
            ->firstOrCreate($this->extractAddress($data));
    }


    private function extractAddress($data)
    {
        return [
            'first_name' => trim((string) $data['first_name']),
            'last_name' => trim((string) $data['last_name']),
            'address_1' => trim((string) $data['address_1']),
            'address_2' => filled($data['address_2'] ?? null)
                ? trim((string) $data['address_2'])
                : null,
            'city' => trim((string) $data['city']),
            'state' => trim((string) $data['state']),
            'zip' => trim((string) $data['zip']),
            'country' => trim((string) $data['country']),
        ];
    }


    private function makeDefaultAddress(Address $address, bool $force = false)
    {
        $defaultAddressId = DefaultAddress::query()
            ->where('customer_id', auth()->id())
            ->value('address_id');

        if (! $force && $defaultAddressId) {
            return;
        }

        DefaultAddress::query()->upsert([
            [
                'customer_id' => auth()->id(),
                'address_id' => $address->id,
            ],
        ], ['customer_id'], ['address_id']);
    }


    private function addShippingMethodToCart($request)
    {
        if (!Cart::allItemsAreVirtual() && !Cart::hasShippingMethod()) {
            Cart::addShippingMethod(ShippingMethod::get($request->shipping_method));
        }
    }


    private function persistOrder($request, array $lines = []): Order
    {
        $snapshot = $this->treatmentBookingSnapshot($request, $lines);

        $this->assertTreatmentSlotsAvailable($request, $lines);

        $cartTotal = Cart::total()->amount();
        $paymentMethod = (string) $request->payment_method;
        $orderTotal = $cartTotal;

        if (ChipPaymentMethodConfig::isChipPaymentMethod($paymentMethod)) {
            $cartSubunit = Money::inCurrentCurrency($cartTotal)->subunit();
            $feeSubunit = app(ChipPaymentMethodsResolver::class)
                ->surchargeSubunit($paymentMethod, null, $cartSubunit);
            $orderTotal = round($cartTotal + ($feeSubunit / 100), 2);
        }

        return Order::create([
                'customer_id' => auth()->id(),
                'customer_email' => $request->customer_email,
                'customer_phone' => PhoneNumber::normalize($request->customer_phone) ?: $request->customer_phone,
                'customer_first_name' => $request->billing['first_name'],
                'customer_last_name' => $request->billing['last_name'],
                'billing_first_name' => $request->billing['first_name'],
                'billing_last_name' => $request->billing['last_name'],
                'billing_address_1' => $request->billing['address_1'],
                'billing_address_2' => $request->billing['address_2'] ?? null,
                'billing_city' => $request->billing['city'],
                'billing_state' => $request->billing['state'],
                'billing_zip' => $request->billing['zip'],
                'billing_country' => $request->billing['country'],
                'shipping_first_name' => $request->shipping['first_name'],
                'shipping_last_name' => $request->shipping['last_name'],
                'shipping_address_1' => $request->shipping['address_1'],
                'shipping_address_2' => $request->shipping['address_2'] ?? null,
                'shipping_city' => $request->shipping['city'],
                'shipping_state' => $request->shipping['state'],
                'shipping_zip' => $request->shipping['zip'],
                'shipping_country' => $request->shipping['country'],
                'sub_total' => Cart::subTotal()->amount(),
                'shipping_method' => Cart::shippingMethod()->name(),
                'shipping_cost' => Cart::shippingCost()->amount(),
                'coupon_id' => Cart::coupon()->id(),
                'discount' => Cart::discount()->amount(),
                'loyalty_points_redeemed' => Cart::hasLoyalty() ? Cart::loyalty()->points() : 0,
                'loyalty_discount_amount' => Cart::hasLoyalty() ? Cart::loyalty()->value()->amount() : 0,
                'total' => $orderTotal,
                'payment_method' => $paymentMethod,
                'currency' => currency(),
                'currency_rate' => CurrencyRate::for(currency()),
                'locale' => locale(),
                'status' => Order::PENDING_PAYMENT,
                'payment_status' => Order::PAYMENT_PENDING,
                'note' => $this->buildOrderNote($request, $snapshot, $lines),
                'beautician_id' => $snapshot['beautician_id'],
                'appointment_date' => $snapshot['appointment_date'],
                'appointment_time' => $snapshot['appointment_time'],
                'schedule_status' => $snapshot['schedule_status'],
                'spa_branch_id' => $request->input('spa_branch_id'),
            ]);
    }


    /**
     * @return list<array{product_id: int, beautician_id: int|null, schedule_later: bool, appointment_date: ?string, appointment_time: ?string, cart_item_id: ?string}>
     */
    private function treatmentBookingLines($request): array
    {
        if (! Cart::hasVirtualTreatment()) {
            return [];
        }

        $raw = $request->input('treatment_bookings');

        if (! is_array($raw) || $raw === []) {
            $scheduleLater = $request->boolean('schedule_later');

            return [[
                'cart_item_id' => null,
                'product_id' => (int) ($this->resolveCartTreatmentProductId() ?? 0),
                'beautician_id' => $request->beautician_id ? (int) $request->beautician_id : null,
                'schedule_later' => $scheduleLater,
                'appointment_date' => $scheduleLater ? null : $request->appointment_date,
                'appointment_time' => $scheduleLater ? null : $this->formatAppointmentTime($request->appointment_time),
            ]];
        }

        $lines = [];

        foreach ($raw as $line) {
            $scheduleLater = filter_var($line['schedule_later'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $time = isset($line['appointment_time']) ? substr((string) $line['appointment_time'], 0, 5) : null;

            $lines[] = [
                'cart_item_id' => isset($line['cart_item_id']) ? (string) $line['cart_item_id'] : null,
                'product_id' => (int) ($line['product_id'] ?? 0),
                'beautician_id' => ! empty($line['beautician_id']) ? (int) $line['beautician_id'] : null,
                'schedule_later' => $scheduleLater,
                'appointment_date' => $scheduleLater ? null : ($line['appointment_date'] ?? null),
                'appointment_time' => $scheduleLater ? null : $this->formatAppointmentTime($time),
            ];
        }

        return $lines;
    }


    /**
     * @param  list<array<string, mixed>>  $lines
     * @return array{beautician_id: ?int, appointment_date: ?string, appointment_time: ?string, schedule_status: ?string, beautician_name: ?string}
     */
    private function treatmentBookingSnapshot($request, array $lines): array
    {
        if ($lines === []) {
            return [
                'beautician_id' => null,
                'appointment_date' => null,
                'appointment_time' => null,
                'schedule_status' => null,
                'beautician_name' => null,
            ];
        }

        $primary = $lines[0];
        $beautician = Beautician::find($primary['beautician_id'] ?? null);

        return [
            'beautician_id' => $primary['beautician_id'] ?? null,
            'appointment_date' => $primary['schedule_later'] ? null : ($primary['appointment_date'] ?? null),
            'appointment_time' => $primary['schedule_later'] ? null : ($primary['appointment_time'] ?? null),
            'schedule_status' => ! empty($primary['schedule_later']) ? 'tba' : null,
            'beautician_name' => $beautician?->name,
        ];
    }


    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function assertTreatmentSlotsAvailable($request, array $lines): void
    {
        if (! Cart::hasVirtualTreatment() || ! app('modules')->isEnabled('TreatmentReservation')) {
            return;
        }

        $spaBranchId = (int) $request->input('spa_branch_id');
        $pendingHolds = [];

        foreach ($lines as $lineIndex => $line) {
            if (! empty($line['schedule_later']) || empty($line['beautician_id']) || empty($line['appointment_date']) || empty($line['appointment_time'])) {
                continue;
            }

            $productId = (int) ($line['product_id'] ?? 0);
            $rawTime = (string) ($line['appointment_time'] ?? '');
            $time = $this->normalizeSlotCheckTime($rawTime);

            if ($productId && $spaBranchId && app('modules')->isEnabled('SpaBranch')) {
                try {
                    app(AppointmentAvailabilityService::class)->assertSlotBookable(
                        $productId,
                        $spaBranchId,
                        (string) $line['appointment_date'],
                        $time,
                        (int) $line['beautician_id'],
                        null,
                        null,
                        $pendingHolds
                    );
                } catch (\InvalidArgumentException $exception) {
                    throw new CheckoutException($exception->getMessage(), previous: $exception);
                }

                $pendingHolds[] = [
                    'beautician_id' => (int) $line['beautician_id'],
                    'appointment_date' => (string) $line['appointment_date'],
                    'appointment_time' => $time,
                    'product_id' => $productId,
                    'duration_minutes' => max(1, app(AppointmentAvailabilityService::class)->resolveDurationMinutes($productId, $spaBranchId)),
                ];

                continue;
            }

            $availability = app(BeauticianAvailabilityService::class);
            $availability->lockAppointmentsForDate((int) $line['beautician_id'], (string) $line['appointment_date']);

            if (! $availability->isSlotAvailable(
                (int) $line['beautician_id'],
                (string) $line['appointment_date'],
                $time
            )) {
                throw new CheckoutException(trans('treatmentreservation::public.slot_unavailable'));
            }

            $pendingHolds[] = [
                'beautician_id' => (int) $line['beautician_id'],
                'appointment_date' => (string) $line['appointment_date'],
                'appointment_time' => $time,
                'product_id' => $productId,
                'duration_minutes' => BeauticianAvailabilityService::SLOT_MINUTES,
            ];
        }
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


    /**
     * @param  array{beautician_id: ?int, appointment_date: ?string, appointment_time: ?string, schedule_status: ?string, beautician_name: ?string}  $snapshot
     * @param  list<array<string, mixed>>  $lines
     */
    private function buildOrderNote($request, array $snapshot, array $lines = []): ?string
    {
        $parts = array_filter([
            $request->order_note,
        ]);

        foreach ($lines as $index => $line) {
            $label = 'Treatment ' . ($index + 1);
            $beautician = Beautician::find($line['beautician_id'] ?? null);
            $lineParts = array_filter([
                $beautician?->name ? "{$label} beautician: {$beautician->name}" : null,
                ! empty($line['schedule_later'])
                    ? "{$label}: TBA"
                    : (($line['appointment_date'] ?? null)
                        ? "{$label} date: " . Carbon::parse($line['appointment_date'])->format('d/M/Y')
                        : null),
                empty($line['schedule_later']) && ! empty($line['appointment_time'])
                    ? "{$label} time: " . $line['appointment_time']
                    : null,
            ]);
            $parts = array_merge($parts, $lineParts);
        }

        if ($lines === [] && $snapshot) {
            $parts = array_merge($parts, array_filter([
                $snapshot['beautician_name'] ? 'Beautician: ' . $snapshot['beautician_name'] : null,
                ($snapshot['schedule_status'] ?? null) === 'tba'
                    ? 'Appt: TBA (to be scheduled)'
                    : ($snapshot['appointment_date']
                        ? 'Appt.Date: ' . Carbon::parse($snapshot['appointment_date'])->format('d/M/Y')
                        : null),
                ($snapshot['schedule_status'] ?? null) === 'tba'
                    ? null
                    : ($snapshot['appointment_time'] ? 'Appt.Time: ' . $snapshot['appointment_time'] : null),
            ]));
        }

        return $parts !== [] ? implode("\n", $parts) : null;
    }


    private function formatAppointmentTime(?string $time): ?string
    {
        return AppointmentTimeFormatter::toStorage($time);
    }


    private function normalizeSlotCheckTime(string $time): string
    {
        $time = trim($time);

        if ($time === '') {
            return '';
        }

        try {
            return Carbon::parse($time)->format('H:i');
        } catch (\Throwable) {
            throw new CheckoutException(trans('treatmentreservation::public.slot_unavailable'));
        }
    }


    private function storeOrderProducts(Order $order)
    {
        Cart::items()->each(function (CartItem $cartItem) use ($order) {
            $order->storeProducts($cartItem);
        });
    }


    private function storeOrderDownloads(Order $order)
    {
        Cart::items()->each(function (CartItem $cartItem) use ($order) {
            $order->storeDownloads($cartItem);
        });
    }


    private function storeFlashSaleProductOrders(Order $order)
    {
        Cart::items()->each(function (CartItem $cartItem) use ($order) {
            if (!FlashSale::contains($cartItem->product)) {
                return;
            }

            FlashSale::pivot($cartItem->product)
                ->orders()
                ->attach([
                    $cartItem->product->id => [
                        'order_id' => $order->id,
                        'qty' => $cartItem->qty,
                    ],
                ]);
        });
    }


    private function incrementCouponUsage()
    {
        Cart::coupon()->usedOnce();
    }


    private function attachTaxes(Order $order)
    {
        Cart::taxes()->each(function (CartTax $cartTax) use ($order) {
            $order->attachTax($cartTax);
        });
    }


    private function captureLoyaltyRedemption(Order $order): void
    {
        if (!app('modules')->isEnabled('Loyalty')) {
            return;
        }

        app(LoyaltyOrderService::class)->captureRedemptionFromCart($order);
    }


    private function refundLoyaltyRedemption(Order $order): void
    {
        if (!app('modules')->isEnabled('Loyalty')) {
            return;
        }

        app(LoyaltyOrderService::class)->refundRedemption($order);
    }
}
