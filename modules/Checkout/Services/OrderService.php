<?php

namespace Modules\Checkout\Services;

use Modules\Cart\CartTax;
use Modules\Cart\CartItem;
use Modules\Cart\Facades\Cart;
use Modules\Order\Entities\Order;
use Modules\Address\Entities\Address;
use Modules\FlashSale\Entities\FlashSale;
use Modules\Currency\Entities\CurrencyRate;
use Modules\Account\Entities\DefaultAddress;
use Modules\Shipping\Facades\ShippingMethod;
use Modules\Beautician\Entities\Beautician;
use Illuminate\Support\Carbon;
use Modules\Loyalty\Services\LoyaltyOrderService;
use Modules\User\Support\PhoneNumber;

class OrderService
{
    public function create($request)
    {
        $this->mergeShippingAddress($request);
        $this->saveAddress($request);
        $this->addShippingMethodToCart($request);

        return tap($this->store($request), function ($order) use ($request) {
            $this->storeOrderProducts($order);
            $this->storeOrderDownloads($order);
            $this->storeFlashSaleProductOrders($order);
            $this->incrementCouponUsage($order);
            $this->attachTaxes($order);
            $this->reduceStock();
        });
    }


    public function reduceStock()
    {
        Cart::reduceStock();
    }


    public function delete(Order $order)
    {
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

        if ($request->newBillingAddress) {
            $address = auth()
                ->user()
                ->addresses()
                ->create($this->extractAddress($request->billing));

            $this->makeDefaultAddress($address);
        }

        if ($request->ship_to_a_different_address && $request->newShippingAddress) {
            auth()
                ->user()
                ->addresses()
                ->create($this->extractAddress($request->shipping));
        }
    }


    private function extractAddress($data)
    {
        return [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'address_1' => $data['address_1'],
            'address_2' => $data['address_2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'],
            'zip' => $data['zip'],
            'country' => $data['country'],
        ];
    }


    private function makeDefaultAddress(Address $address)
    {
        if (
            auth()
                ->user()
                ->addresses()
                ->count() > 1
        ) {
            return;
        }

        DefaultAddress::create([
            'address_id' => $address->id,
            'customer_id' => auth()->id(),
        ]);
    }


    private function addShippingMethodToCart($request)
    {
        if (!Cart::allItemsAreVirtual() && !Cart::hasShippingMethod()) {
            Cart::addShippingMethod(ShippingMethod::get($request->shipping_method));
        }
    }


    private function store($request)
    {
        $booking = $this->treatmentBookingData($request);

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
            'total' => Cart::total()->amount(),
            'payment_method' => $request->payment_method,
            'currency' => currency(),
            'currency_rate' => CurrencyRate::for(currency()),
            'locale' => locale(),
            'status' => Order::PENDING_PAYMENT,
            'payment_status' => Order::PAYMENT_PENDING,
            'note' => $this->buildOrderNote($request, $booking),
            'beautician_id' => $booking['beautician_id'],
            'appointment_date' => $booking['appointment_date'],
            'appointment_time' => $booking['appointment_time'],
            'spa_branch_id' => $request->input('spa_branch_id'),
        ]);
    }


    /**
     * @return array{beautician_id: ?int, appointment_date: ?string, appointment_time: ?string, beautician_name: ?string}
     */
    private function treatmentBookingData($request): array
    {
        if (! Cart::hasVirtualTreatment()) {
            return [
                'beautician_id' => null,
                'appointment_date' => null,
                'appointment_time' => null,
                'beautician_name' => null,
            ];
        }

        $beautician = Beautician::find($request->beautician_id);

        return [
            'beautician_id' => $request->beautician_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $this->formatAppointmentTime($request->appointment_time),
            'beautician_name' => $beautician?->name,
        ];
    }


    private function buildOrderNote($request, array $booking): ?string
    {
        $parts = array_filter([
            $request->order_note,
            $booking['beautician_name'] ? 'Beautician: ' . $booking['beautician_name'] : null,
            $booking['appointment_date']
                ? 'Appt.Date: ' . Carbon::parse($booking['appointment_date'])->format('d/M/Y')
                : null,
            $booking['appointment_time'] ? 'Appt.Time: ' . $booking['appointment_time'] : null,
        ]);

        return $parts !== [] ? implode("\n", $parts) : null;
    }


    private function formatAppointmentTime(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i', $time)->format('h:i A');
        } catch (\Exception) {
            return $time;
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
