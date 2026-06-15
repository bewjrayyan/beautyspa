<?php

namespace Modules\TreatmentReservation\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Beautician\Entities\Beautician;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;
use Modules\Product\Entities\Product;
use Modules\Support\Eloquent\Model;
use Modules\Support\Money;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;
use Modules\TreatmentReservation\Services\BookingCrmInsightService;
use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
use Modules\User\Services\OneSenderWhatsAppService;
use Modules\User\Support\PhoneNumber;

class TreatmentBooking extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELED = 'canceled';

    public const SOURCE_CHECKOUT = 'checkout';

    public const SOURCE_ADMIN_MANUAL = 'admin_manual';

    public const SOURCE_PORTAL_MANUAL = 'portal_manual';

    public const PAYMENT_DEPOSIT = 'deposit';

    public const PAYMENT_PARTIAL = 'partial_payment';

    public const PAYMENT_FULL_PAID = 'full_paid';

    protected $fillable = [
        'order_id',
        'source',
        'created_by_user_id',
        'beautician_id',
        'treatment_category_id',
        'product_id',
        'variant_id',
        'product_options',
        'product_variations',
        'customer_first_name',
        'customer_last_name',
        'customer_phone',
        'customer_email',
        'appointment_date',
        'appointment_time',
        'status',
        'total',
        'currency',
        'payment_status',
        'payment_receipt_file_id',
        'notes',
        'beautician_notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'total' => 'float',
        'product_options' => 'array',
        'product_variations' => 'array',
        'deleted_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'customer_reminder_sent_at' => 'datetime',
        'completed_notification_sent_at' => 'datetime',
        'followup_sent_at' => 'datetime',
    ];


    public static function kanbanStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
        ];
    }


    /**
     * @return array<int, string>
     */
    public static function manualSources(): array
    {
        return [
            self::SOURCE_ADMIN_MANUAL,
            self::SOURCE_PORTAL_MANUAL,
        ];
    }


    /**
     * @return array<int, string>
     */
    public static function manualPaymentStatuses(): array
    {
        return [
            self::PAYMENT_DEPOSIT,
            self::PAYMENT_PARTIAL,
            self::PAYMENT_FULL_PAID,
        ];
    }


    /**
     * @return array<int, string>
     */
    public static function manualPaymentStatusesRequiringReceipt(): array
    {
        return [
            self::PAYMENT_PARTIAL,
            self::PAYMENT_FULL_PAID,
        ];
    }


    public static function normalizeManualPaymentStatus(?string $status): string
    {
        if (in_array($status, self::manualPaymentStatuses(), true)) {
            return $status;
        }

        return match ($status) {
            Order::PAYMENT_PAID => self::PAYMENT_FULL_PAID,
            Order::PAYMENT_PROCESSING => self::PAYMENT_PARTIAL,
            default => self::PAYMENT_DEPOSIT,
        };
    }


    public function isManualBooking(): bool
    {
        return in_array($this->source, self::manualSources(), true);
    }


    public function isManualEditable(): bool
    {
        return $this->isManualBooking()
            && in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS], true);
    }


    public function canRescheduleManual(): bool
    {
        return $this->isManualBooking()
            && $this->status === self::STATUS_PENDING;
    }


    public static function statusFromOrder(string $orderStatus, ?string $paymentStatus = null): string
    {
        if (in_array($orderStatus, [Order::CANCELED, Order::REFUNDED], true)
            || $paymentStatus === Order::PAYMENT_CANCELED) {
            return self::STATUS_CANCELED;
        }

        if (in_array($orderStatus, [Order::PROCESSING, Order::ON_HOLD], true)) {
            return self::STATUS_IN_PROGRESS;
        }

        // Order may be "completed" after online payment; treatment is still pending.
        if ($orderStatus === Order::COMPLETED && $paymentStatus === Order::PAYMENT_PAID) {
            return self::STATUS_PENDING;
        }

        if ($orderStatus === Order::COMPLETED) {
            return self::STATUS_COMPLETED;
        }

        return self::STATUS_PENDING;
    }


    public function beautician()
    {
        return $this->belongsTo(Beautician::class);
    }


    public function category()
    {
        return $this->belongsTo(TreatmentCategory::class, 'treatment_category_id');
    }


    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function paymentReceipt()
    {
        return $this->belongsTo(\Modules\Media\Entities\File::class, 'payment_receipt_file_id');
    }


    public function order()
    {
        return $this->belongsTo(Order::class);
    }


    public function createdBy()
    {
        return $this->belongsTo(\Modules\User\Entities\User::class, 'created_by_user_id');
    }


    public function activities()
    {
        return $this->hasMany(TreatmentBookingActivity::class)->latest();
    }


    /**
     * @return array<string, mixed>
     */
    public function appendAdminPayload(array $payload): array
    {
        $payload['recent_activities'] = $this->activities()
            ->with('user')
            ->limit(10)
            ->get()
            ->map->toPayload()
            ->values()
            ->all();

        return app(BookingCrmInsightService::class)->enrichPayload($this, $payload);
    }


    public function getCustomerFullNameAttribute(): string
    {
        return trim("{$this->customer_first_name} {$this->customer_last_name}");
    }


    public function getTotalMoneyAttribute(): ?Money
    {
        if ($this->total === null) {
            return null;
        }

        return Money::inDefaultCurrency($this->total);
    }


    /**
     * Job sheet / calendar: hide bookings whose order was deleted (soft or hard).
     */
    public function scopeWithActiveOrder(Builder $query): Builder
    {
        return $query->where(function (Builder $inner) {
            $inner->whereNull('order_id')
                ->orWhereHas('order');
        });
    }


    public function scopeWithTreatmentProduct(Builder $query, bool $withTreatment = true): Builder
    {
        if (! $withTreatment) {
            return $query->where(function (Builder $query) {
                $query->whereNull('product_id')
                    ->orWhereDoesntHave('product', fn (Builder $productQuery) => $productQuery->where('is_virtual', true));
            });
        }

        return $query
            ->whereNotNull('product_id')
            ->whereHas('product', fn (Builder $productQuery) => $productQuery->where('is_virtual', true));
    }


    public function scopeForCalendar(Builder $query, string $month, ?int $beauticianId = null, ?int $categoryId = null): Builder
    {
        $start = \Illuminate\Support\Carbon::parse($month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return $query
            ->withActiveOrder()
            ->withTreatmentProduct()
            ->with([
                'beautician.files',
                'beautician.user',
                'beautician.spaBranches',
                'category',
                'product.attributes.attribute',
                'product.attributes.values.attributeValue',
            ])
            ->whereNotNull('appointment_date')
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->whereNot('status', self::STATUS_CANCELED)
            ->when($beauticianId, fn (Builder $q) => $q->where('beautician_id', $beauticianId))
            ->when($categoryId, fn (Builder $q) => $q->where('treatment_category_id', $categoryId))
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');
    }


    public function scopeForKanban(Builder $query, ?int $beauticianId = null, ?int $categoryId = null): Builder
    {
        return $query
            ->withActiveOrder()
            ->withTreatmentProduct()
            ->with(['beautician.files', 'beautician.user', 'beautician.spaBranches', 'category', 'product', 'order.products.product'])
            ->whereIn('status', self::kanbanStatuses())
            ->when($beauticianId, fn (Builder $q) => $q->where('beautician_id', $beauticianId))
            ->when($categoryId, fn (Builder $q) => $q->where('treatment_category_id', $categoryId))
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');
    }


    public function scopeMatchingCustomerPhone(Builder $query, string $normalizedPhone): Builder
    {
        $variants = PhoneNumber::variants($normalizedPhone);

        if ($variants === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $inner) use ($variants) {
            foreach ($variants as $variant) {
                $inner->orWhere('customer_phone', $variant);
            }
        });
    }


    public function toCalendarPayload(): array
    {
        return array_merge($this->sharedDetailPayload(), [
            'date' => $this->appointment_date?->format('Y-m-d'),
            'time' => $this->appointment_time,
        ]);
    }


    public static function statusAccentColor(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => '#ea580c',
            self::STATUS_IN_PROGRESS => '#4338ca',
            self::STATUS_COMPLETED => '#047857',
            self::STATUS_CANCELED => '#b91c1c',
            default => '#94a3b8',
        };
    }


    public function toKanbanPayload(): array
    {
        return array_merge($this->sharedDetailPayload(), [
            'appointment_time' => $this->appointment_time,
            'category_color' => $this->category?->color ?? '#6366f1',
        ]);
    }


    /**
     * @return array<string, mixed>
     */
    public function sharedDetailPayload(): array
    {
        $treatmentLine = $this->treatmentLineMeta();
        $slotDurationMinutes = $this->resolveSlotDurationMinutes();

        return [
            'id' => $this->id,
            'status' => $this->status,
            'customer_name' => $this->customer_full_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'can_whatsapp_customer' => filled(trim((string) $this->customer_phone))
                && OneSenderWhatsAppService::isConfigured(),
            'treatment_name' => $treatmentLine['product_name'],
            'product_name' => $treatmentLine['product_name'],
            'treatment_selection' => $treatmentLine['treatment_selection'],
            'treatment_subtitle' => $this->agendaSubtitleLine($treatmentLine, $slotDurationMinutes),
            'slot_duration_minutes' => $slotDurationMinutes,
            'duration_session_label' => TrLang::trans('admin.crm.agenda_duration_session', ['count' => $slotDurationMinutes]),
            'appointment_date' => $this->appointment_date?->format('d M Y'),
            'appointment_time' => $this->appointment_time,
            'time' => $this->appointment_time,
            'appointment_end_time' => $this->appointmentEndTime(),
            'appointment_time_range' => $this->appointmentTimeRange(),
            'beautician_id' => $this->beautician_id,
            'beautician_name' => $this->beautician?->name ?? '—',
            'beautician_job_title' => filled(trim((string) ($this->beautician?->job_title ?? '')))
                ? trim((string) $this->beautician->job_title)
                : null,
            'beautician_color' => $this->beautician?->profile_color ?: '#6366f1',
            'beautician_avatar' => $this->beautician?->displayAvatarUrl(),
            'beautician_initial' => $this->beautician?->initials ?? '?',
            'beautician_phone_available' => filled(trim((string) ($this->beautician?->phone ?? ''))),
            'category_name' => $this->category?->name,
            'category_color' => $this->category?->color ?? '#6366f1',
            'status_accent' => self::statusAccentColor($this->status),
            'total_formatted' => Money::inDefaultCurrency($this->total ?? 0)->format(),
            'payment_status_label' => $this->paymentStatusLabel(),
            'notes' => $this->notes,
            'beautician_notes' => $this->beautician_notes,
            'order_id' => $this->order_id,
            'order_url' => $this->order_id
                ? route('admin.orders.show', $this->order_id)
                : null,
            'source' => $this->source ?? self::SOURCE_CHECKOUT,
            'source_label' => $this->sourceLabel(),
            'spa_branch_name' => $this->spaBranchLabel(),
            'is_manual' => $this->isManualBooking(),
            'can_edit_manual' => $this->isManualEditable(),
            'can_cancel_manual' => $this->isManualEditable(),
            'customer_first_name' => $this->customer_first_name,
            'customer_last_name' => $this->customer_last_name,
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'product_options' => $this->product_options ?? [],
            'product_variations' => $this->product_variations ?? [],
            'payment_status' => $this->resolvedPaymentStatus(),
            'payment_is_outstanding' => $this->hasOutstandingPayment(),
            'payment_receipt_url' => $this->paymentReceipt?->path,
            'appointment_date_value' => $this->appointment_date?->format('Y-m-d'),
        ];
    }


    public function formattedAppointmentTime(): string
    {
        if (! filled($this->appointment_time)) {
            return '';
        }

        try {
            return \Illuminate\Support\Carbon::parse($this->appointment_time)->format('H:i');
        } catch (\Throwable) {
            return (string) $this->appointment_time;
        }
    }


    public function displayAppointmentTime(): string
    {
        return filled($this->appointment_time) ? trim((string) $this->appointment_time) : '';
    }


    public function appointmentEndTime(): ?string
    {
        $start = $this->displayAppointmentTime();

        if ($start === '') {
            return null;
        }

        try {
            $minutes = $this->resolveSlotDurationMinutes();
            $parsed = \Illuminate\Support\Carbon::parse($start);

            return $parsed->copy()->addMinutes($minutes)->format(
                preg_match('/[AP]M/i', $start) ? 'g:i A' : 'H:i'
            );
        } catch (\Throwable) {
            return null;
        }
    }


    public function appointmentTimeRange(): ?string
    {
        $start = $this->displayAppointmentTime();

        if ($start === '') {
            return null;
        }

        $end = $this->appointmentEndTime();

        return $end ? $start . ' – ' . $end : $start;
    }


    public function sourceLabel(): string
    {
        return match ($this->source ?? self::SOURCE_CHECKOUT) {
            self::SOURCE_ADMIN_MANUAL => TrLang::trans('admin.crm.source_admin_manual'),
            self::SOURCE_PORTAL_MANUAL => TrLang::trans('admin.crm.source_portal_manual'),
            default => TrLang::trans('admin.crm.source_checkout'),
        };
    }


    public function spaBranchLabel(): ?string
    {
        if (! is_module_enabled('SpaBranch')) {
            return null;
        }

        $this->loadMissing('beautician.spaBranches');

        $names = $this->beautician?->spaBranches
            ->pluck('name')
            ->filter(fn ($name) => filled(trim((string) $name)))
            ->values();

        if ($names->isEmpty()) {
            return null;
        }

        return $names->implode(', ');
    }


    public function ledgerLineTotal(): Money
    {
        if ($this->total !== null && (float) $this->total > 0) {
            return Money::inDefaultCurrency($this->total);
        }

        $line = $this->resolveTreatmentOrderProduct();

        if ($line?->line_total) {
            return $line->line_total;
        }

        $this->loadMissing('product');

        if ($this->product) {
            return $this->product->selling_price;
        }

        return Money::inDefaultCurrency(0);
    }


    public function resolvedPaymentStatus(): string
    {
        if ($this->isManualBooking()) {
            return self::normalizeManualPaymentStatus($this->payment_status);
        }

        $this->loadMissing('order');

        if ($this->order_id && filled($this->order?->payment_status)) {
            return (string) $this->order->payment_status;
        }

        if (filled($this->payment_status)) {
            return (string) $this->payment_status;
        }

        return Order::PAYMENT_PENDING;
    }


    public function hasOutstandingPayment(): bool
    {
        if ($this->isManualBooking()) {
            return $this->resolvedPaymentStatus() !== self::PAYMENT_FULL_PAID;
        }

        return ! in_array($this->resolvedPaymentStatus(), [Order::PAYMENT_PAID], true);
    }


    public function paymentStatusLabel(): string
    {
        if ($this->isManualBooking()) {
            $status = $this->resolvedPaymentStatus();
            $key = 'treatmentreservation::admin.manual_booking.payment_statuses.' . $status;

            return trans($key) === $key
                ? ucfirst(str_replace('_', ' ', $status))
                : trans($key);
        }

        $status = $this->resolvedPaymentStatus();
        $key = 'order::payment_statuses.' . $status;

        return trans($key) === $key
            ? ucfirst(str_replace('_', ' ', $status))
            : trans($key);
    }


    public function resolveSlotDurationMinutes(): int
    {
        $this->loadMissing([
            'product.attributes.attribute',
            'product.attributes.values.attributeValue',
        ]);

        foreach ($this->product?->attributes ?? [] as $productAttribute) {
            $slug = $productAttribute->attribute?->slug ?? '';

            if (! in_array($slug, ['spa-duration', 'duration'], true)) {
                continue;
            }

            $value = $productAttribute->values->first()?->value ?? '';

            if (preg_match('/(\d+)/', (string) $value, $matches)) {
                return max(1, (int) $matches[1]);
            }
        }

        return BeauticianAvailabilityService::SLOT_MINUTES;
    }


    /**
     * @param  array{product_name: string, treatment_selection: string|null}  $treatmentLine
     */
    private function agendaSubtitleLine(array $treatmentLine, int $slotDurationMinutes): ?string
    {
        $parts = array_values(array_filter([
            $treatmentLine['treatment_selection'] ?? null,
            TrLang::trans('admin.crm.agenda_duration_session', ['count' => $slotDurationMinutes]),
        ]));

        if ($parts !== []) {
            return implode(' · ', $parts);
        }

        $fallback = array_values(array_filter([
            $this->category?->name,
            TrLang::trans('admin.crm.agenda_duration_session', ['count' => $slotDurationMinutes]),
        ]));

        if ($fallback !== []) {
            return implode(' · ', $fallback);
        }

        return null;
    }


    /**
     * @return array{product_name: string, treatment_selection: string|null}
     */
    public function treatmentLineMeta(): array
    {
        $productName = $this->product?->name ?? '—';
        $selectionParts = [];
        $orderProduct = $this->resolveTreatmentOrderProduct();

        if ($orderProduct) {
            $productName = $orderProduct->name ?: $productName;

            if ($orderProduct->hasAnyOption()) {
                foreach ($orderProduct->options as $option) {
                    if ($option->isFieldType()) {
                        if (filled($option->value)) {
                            $selectionParts[] = (string) $option->value;
                        }
                    } else {
                        $labels = $option->values->implode('label', ', ');

                        if ($labels !== '') {
                            $selectionParts[] = $labels;
                        }
                    }
                }
            }

            if ($selectionParts === [] && $orderProduct->hasAnyVariation()) {
                foreach ($orderProduct->variations as $variation) {
                    $value = $variation->values->first()?->label ?? $variation->value;

                    if (filled($value)) {
                        $selectionParts[] = (string) $value;
                    }
                }
            }
        } elseif ($this->isManualBooking()) {
            $selectionParts = $this->manualSelectionSummary();
        }

        return [
            'product_name' => $productName,
            'treatment_selection' => $selectionParts !== [] ? implode(' · ', $selectionParts) : null,
        ];
    }


    /**
     * @return array<int, string>
     */
    private function manualSelectionSummary(): array
    {
        $parts = [];
        $product = $this->relationLoaded('product')
            ? $this->product
            : Product::with(['options.values', 'variations.values'])->find($this->product_id);

        if (! $product) {
            return $parts;
        }

        $variations = $this->product_variations ?? [];

        foreach ($product->variations as $variation) {
            $selectedUid = $variations[$variation->uid] ?? null;

            if (! $selectedUid) {
                continue;
            }

            $value = $variation->values->firstWhere('uid', $selectedUid);

            if ($value) {
                $parts[] = $value->label;
            }
        }

        $options = $this->product_options ?? [];

        foreach ($product->options as $option) {
            $selected = $options[$option->id] ?? null;

            if ($selected === null || $selected === '') {
                continue;
            }

            if (in_array($option->type, ['field', 'textarea', 'date', 'date_time', 'time'], true)) {
                $parts[] = (string) $selected;

                continue;
            }

            $selectedValues = is_array($selected) ? $selected : [$selected];

            foreach ($selectedValues as $selectedValue) {
                $value = $option->values->firstWhere('id', (int) $selectedValue);

                if ($value) {
                    $parts[] = $value->label;
                }
            }
        }

        return $parts;
    }


    private function resolveTreatmentOrderProduct(): ?OrderProduct
    {
        if (! $this->order_id) {
            return null;
        }

        $this->loadMissing(['order.products.product']);

        foreach ($this->order?->products ?? [] as $line) {
            if ($this->product_id && (int) $line->product_id === (int) $this->product_id) {
                return $line;
            }

            if ($line->product?->is_virtual) {
                return $line;
            }
        }

        return null;
    }
}
