<?php

namespace Modules\Order\Entities;

use Modules\Cart\CartTax;
use Modules\Cart\CartItem;
use Modules\Support\Money;
use Modules\Support\State;
use Modules\Support\Country;
use Modules\Media\Entities\File;
use Modules\Tax\Entities\TaxRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Order\OrderCollection;
use Modules\Coupon\Entities\Coupon;
use Modules\Order\Admin\OrderTable;
use Modules\Support\Eloquent\Model;
use Modules\Payment\Services\PaymentMethodLabel;
use Modules\Payment\HasTransactionReference;
use Modules\Shipping\Facades\ShippingMethod;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Transaction\Entities\Transaction;
use Modules\Beautician\Entities\Beautician;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

class Order extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (Order $order) {
            if ($order->customer_phone !== null && $order->customer_phone !== '') {
                $order->customer_phone = PhoneNumber::normalize($order->customer_phone);
            }
        });
    }

    const CANCELED = 'canceled';
    const COMPLETED = 'completed';
    const ON_HOLD = 'on_hold';
    const PENDING = 'pending';
    const PENDING_PAYMENT = 'pending_payment';
    const PROCESSING = 'processing';
    const REFUNDED = 'refunded';

    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PROCESSING = 'processing';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_CANCELED = 'canceled';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'customer_email',
        'customer_phone',
        'customer_first_name',
        'customer_last_name',
        'billing_first_name',
        'billing_last_name',
        'billing_address_1',
        'billing_address_2',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_country',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_address_1',
        'shipping_address_2',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'shipping_country',
        'sub_total',
        'shipping_method',
        'shipping_cost',
        'coupon_id',
        'discount',
        'total',
        'payment_method',
        'currency',
        'currency_rate',
        'locale',
        'status',
        'payment_status',
        'note',
        'beautician_id',
        'appointment_date',
        'appointment_time',
        'spa_branch_id',
        'start_date',
        'end_date',
        'tracking_number',
        'loyalty_points_redeemed',
        'loyalty_discount',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'appointment_date' => 'date',
        'google_sheets_synced_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    public static function totalSales()
    {
        return Money::inDefaultCurrency(self::withoutCanceledOrders()->sum('total'));
    }


    public function status()
    {
        return trans("order::statuses.{$this->status}");
    }


    public function paymentStatusLabel(): string
    {
        $status = $this->payment_status;

        if ($status === null || $status === '') {
            return trans('order::payment_statuses.pending');
        }

        $key = "order::payment_statuses.{$status}";

        return trans($key) === $key
            ? ucfirst(str_replace('_', ' ', $status))
            : trans($key);
    }


    public static function paymentStatuses(): array
    {
        return [
            self::PAYMENT_PENDING,
            self::PAYMENT_PROCESSING,
            self::PAYMENT_PAID,
            self::PAYMENT_CANCELED,
        ];
    }


    public function isPaymentPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }


    public function hasShippingMethod()
    {
        return !is_null($this->shipping_method);
    }


    public function allProductsAreVirtual(): bool
    {
        return $this->products->isNotEmpty()
            && $this->products->every(fn ($line) => (bool) $line->product?->is_virtual);
    }


    public function hasPhysicalProducts(): bool
    {
        return $this->products->contains(
            fn ($line) => ! (bool) $line->product?->is_virtual
        );
    }


    public function loyaltyDiscountAmount(): Money
    {
        return Money::inDefaultCurrency((float) ($this->attributes['loyalty_discount_amount'] ?? 0));
    }


    public function hasLoyaltyRedemption(): bool
    {
        return (int) ($this->loyalty_points_redeemed ?? 0) > 0
            && $this->loyaltyDiscountAmount()->amount() > 0;
    }


    public function paymentProcessingFee(): Money
    {
        $amount = $this->sub_total->amount();

        if ($this->hasShippingMethod()) {
            $amount += $this->shipping_cost->amount();
        }

        foreach ($this->taxes as $tax) {
            $amount += $tax->order_tax->amount->amount();
        }

        if ($this->hasCoupon()) {
            $amount -= $this->discount->amount();
        }

        if ($this->hasLoyaltyRedemption()) {
            $amount -= $this->loyaltyDiscountAmount()->amount();
        }

        $fee = round($this->total->amount() - $amount, 4);

        return Money::inDefaultCurrency(max(0, $fee));
    }


    public function hasPaymentProcessingFee(): bool
    {
        return $this->paymentProcessingFee()->amount() > 0.009;
    }


    /**
     * Checkout free-text note only (excludes Beautician / Appt lines stored in note for exports).
     */
    public function customerOrderNote(): ?string
    {
        if ($this->note === null || trim($this->note) === '') {
            return null;
        }

        $customerLines = [];

        foreach (preg_split('/\r\n|\r|\n/', $this->note) as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^Beautician:\s/i', $trimmed)) {
                continue;
            }

            if (preg_match('/^Appt\.?\s*Date:\s/i', $trimmed)) {
                continue;
            }

            if (preg_match('/^Appt\.?\s*Time:\s/i', $trimmed)) {
                continue;
            }

            $customerLines[] = $trimmed;
        }

        if ($customerLines === []) {
            return null;
        }

        return implode("\n", $customerLines);
    }


    /**
     * Auto-appended appointment lines in note (export / integrations).
     */
    public function exportOrderNoteLines(): ?string
    {
        if ($this->note === null || trim($this->note) === '') {
            return null;
        }

        $exportLines = [];

        foreach (preg_split('/\r\n|\r|\n/', $this->note) as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^Beautician:\s/i', $trimmed)
                || preg_match('/^Appt\.?\s*Date:\s/i', $trimmed)
                || preg_match('/^Appt\.?\s*Time:\s/i', $trimmed)) {
                $exportLines[] = $trimmed;
            }
        }

        if ($exportLines === []) {
            return null;
        }

        return implode("\n", $exportLines);
    }


    public function hasAppointmentDetails(): bool
    {
        return $this->beautician_id
            || $this->appointment_date
            || filled($this->appointment_time);
    }


    public function hasCoupon()
    {
        return !is_null($this->coupon);
    }


    public function totalTax()
    {
        $total = 0;

        if ($this->hasTax()) {
            $this->taxes()
                ->get()
                ->each(function ($tax) use (&$total) {
                    $total += $tax->order_tax->amount->amount();
                });
        }

        return Money::inDefaultCurrency($total);
    }


    public function hasTax()
    {
        return $this->taxes->isNotEmpty();
    }


    public function taxes()
    {
        return $this->belongsToMany(TaxRate::class, 'order_taxes')
            ->using(OrderTax::class)
            ->as('order_tax')
            ->withPivot('amount')
            ->withTrashed();
    }


    public function salesAnalytics()
    {
        return $this->normalizeOrders($this->ordersByWeekDay())->mapWithKeys(function ($orders, $weekDay) {
            return [$weekDay => $this->dataForChart($orders)];
        });
    }


    public function coupon()
    {
        return $this->belongsTo(Coupon::class)->withTrashed();
    }


    public function beautician()
    {
        return $this->belongsTo(Beautician::class);
    }

    public function spaBranch()
    {
        return $this->belongsTo(SpaBranch::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function customerAvatarUrl(): ?string
    {
        return $this->customer?->avatarUrl();
    }

    public function customerAvatarInitial(): string
    {
        if ($this->customer) {
            return $this->customer->avatarInitial();
        }

        $first = mb_substr(trim((string) $this->customer_first_name), 0, 1);
        $last = mb_substr(trim((string) $this->customer_last_name), 0, 1);
        $initials = strtoupper($first . $last);

        return $initials !== '' ? $initials : '?';
    }

    public function customerAvatarBackgroundColor(): string
    {
        if ($this->customer) {
            return $this->customer->avatarBackgroundColor();
        }

        $seed = $this->customer_email ?: (string) $this->id;

        return '#' . substr(md5($seed), 0, 6);
    }


    public function getSubTotalAttribute($subTotal)
    {
        return Money::inDefaultCurrency($subTotal);
    }


    public function getShippingCostAttribute($shippingCost)
    {
        return Money::inDefaultCurrency($shippingCost);
    }


    public function getDiscountAttribute($discount)
    {
        return Money::inDefaultCurrency($discount);
    }


    public function getTaxAttribute($tax)
    {
        return Money::inDefaultCurrency($tax);
    }


    public function getTotalAttribute($total)
    {
        return Money::inDefaultCurrency($total);
    }


    /**
     * Get the order's shipping method.
     *
     * @param string $shippingMethod
     *
     * @return string
     */
    public function getShippingMethodAttribute($shippingMethod)
    {
        return ShippingMethod::get($shippingMethod)->label ?? null;
    }


    /**
     * Get the order's payment method.
     *
     * @param string $paymentMethod
     *
     * @return string
     */
    public function getPaymentMethodAttribute($paymentMethod)
    {
        return PaymentMethodLabel::resolve($paymentMethod);
    }


    public function getCustomerFullNameAttribute()
    {
        return "{$this->customer_first_name} {$this->customer_last_name}";
    }


    public function isReturningCustomer(): bool
    {
        if (! $this->customer_id && ! filled($this->customer_email)) {
            return false;
        }

        return $this->priorOrdersQuery()->exists();
    }


    public function isNewCustomer(): bool
    {
        if (! $this->customer_id && ! filled($this->customer_email)) {
            return false;
        }

        return ! $this->isReturningCustomer();
    }


    public function customerRecencyBadgeLabel(): string
    {
        return $this->isReturningCustomer()
            ? trans('order::orders.returning_customer')
            : trans('order::orders.new_customer');
    }


    public function getBillingFullNameAttribute()
    {
        return "{$this->billing_first_name} {$this->billing_last_name}";
    }


    public function getShippingFullNameAttribute()
    {
        return "{$this->shipping_first_name} {$this->shipping_last_name}";
    }


    public function getBillingCountryNameAttribute()
    {
        return Country::name($this->billing_country);
    }


    public function getShippingCountryNameAttribute()
    {
        return Country::name($this->shipping_country);
    }


    public function getBillingStateNameAttribute()
    {
        return State::name($this->billing_country, $this->billing_state);
    }


    public function getShippingStateNameAttribute()
    {
        return State::name($this->shipping_country, $this->shipping_state);
    }


    public function scopeWithoutCanceledOrders($query)
    {
        return $query->whereNotIn('status', [self::CANCELED, self::REFUNDED]);
    }


    public function storeProducts(CartItem $cartItem)
    {
        $orderProduct = $this->products()->create([
            'product_id' => $cartItem->product->id,
            'product_variant_id' => $cartItem->variant?->id,
            'unit_price' => $cartItem->unitPrice()->amount(),
            'qty' => $cartItem->qty,
            'line_total' => $cartItem->totalPrice()->amount(),
        ]);

        $orderProduct->storeVariations($cartItem->variations);
        $orderProduct->storeOptions($cartItem->options);
    }


    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }


    public function storeDownloads(CartItem $cartItem)
    {
        $cartItem->product->downloads->each(function (File $file) {
            $this->downloads()->create(['file_id' => $file->id]);
        });
    }


    public function downloads()
    {
        return $this->hasMany(OrderDownload::class);
    }


    public function attachTax(CartTax $cartTax)
    {
        $this->taxes()->attach($cartTax->id(), ['amount' => $cartTax->amount()->amount()]);
    }


    public function storeTransaction($response)
    {
        if (!$response instanceof HasTransactionReference) {
            return;
        }

        $this->transaction()->updateOrCreate(
            ['order_id' => $this->id],
            [
                'transaction_id' => $response->getTransactionReference(),
                'payment_method' => $this->attributes['payment_method'],
            ]
        );
    }


    public function transaction()
    {
        return $this->hasOne(Transaction::class)->withTrashed();
    }


    /**
     * Get table data for the resource
     *
     * @return JsonResponse
     */
    public function table(Request $request): OrderTable
    {
        $query = $this->newQuery()->select([
            'id',
            'customer_first_name',
            'customer_last_name',
            'customer_email',
            'currency',
            'total',
            'status',
            'payment_status',
            'spa_branch_id',
            'created_at',
            'deleted_at',
        ])->with('spaBranch:id,name');

        if ($request->boolean('archived')) {
            $query->onlyTrashed();
        }

        return new OrderTable($query);
    }


    private function normalizeOrders($orders)
    {
        return Collection::times(7)->map(function ($dayOfWeek) use ($orders) {
            return new OrderCollection($orders[now()->subDays(7 - $dayOfWeek)->weekday()] ?? []);
        });
    }


    private function ordersByWeekDay()
    {
        return self::select('total', 'created_at')
            ->withoutCanceledOrders()
            ->whereBetween('created_at', [now()->subDays(6), now()->addDay()])
            ->get()
            ->reduce(function ($ordersByWeekDay, $order) {
                $ordersByWeekDay[$order->created_at->weekday()][] = $order;

                return $ordersByWeekDay;
            });
    }


    private function dataForChart(OrderCollection $orders)
    {
        return [
            'total' => $orders->sumTotal(),
            'total_orders' => $orders->count(),
        ];
    }

    protected function priorOrdersQuery()
    {
        $query = static::query()
            ->withTrashed()
            ->whereKeyNot($this->id);

        if ($this->customer_id) {
            return $query->where(function ($customerQuery) {
                $customerQuery->where('customer_id', $this->customer_id);

                if (filled($this->customer_email)) {
                    $customerQuery->orWhere('customer_email', $this->customer_email);
                }
            });
        }

        if (filled($this->customer_email)) {
            return $query->where('customer_email', $this->customer_email);
        }

        return $query->whereRaw('0 = 1');
    }


    /**
     * Allow admin routes to resolve soft-deleted orders (reports, print, status).
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->resolveRouteBindingQuery($this, $value, $field)
            ->withTrashed()
            ->first();
    }
}
