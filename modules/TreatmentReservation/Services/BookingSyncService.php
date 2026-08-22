<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Modules\Beautician\Entities\Beautician;
use Modules\Checkout\Services\CheckoutCompletionGuard;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;
use Modules\Product\Entities\Product;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Support\AppointmentTimeFormatter;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;
use Modules\TreatmentReservation\Services\CheckoutTreatmentScheduleHolds;
use Modules\User\Support\PhoneNumber;

class BookingSyncService
{
    public static bool $syncingFromOrder = false;

    /** @var array<int, array<string, mixed>>|null Pending checkout lines for order-create race. */
    public static ?array $pendingCheckoutLines = null;

    /** Skip OrderTreatmentBookingObserver while CheckoutPaymentFinalizer creates bookings. */
    public static bool $suppressOrderObserverSync = false;

    private const PENDING_CHECKOUT_LINES_CACHE_PREFIX = 'checkout_treatment_lines:';

    private const PENDING_CHECKOUT_LINES_TTL_HOURS = 48;

    public function __construct(
        private AppointmentAvailabilityService $availability,
    ) {}

    /**
     * Refresh bookings from order. Schedules are per-booking (checkout lines), not order snapshot.
     *
     * @return TreatmentBooking|null First booking (compat)
     */
    public function syncFromOrder(Order $order): ?TreatmentBooking
    {
        $order->loadMissing(['products.product']);

        $virtualProducts = $this->virtualOrderProducts($order);

        if ($virtualProducts->isEmpty()) {
            if ($order->beautician_id || $order->appointment_date || $order->schedule_status === 'tba') {
                if (is_array(self::$pendingCheckoutLines) && self::$pendingCheckoutLines !== []) {
                    return $this->syncCheckoutLines($order, self::$pendingCheckoutLines)->first();
                }

                return TreatmentBooking::query()->where('order_id', $order->id)->orderBy('id')->first();
            }

            $this->trashBookingsForOrder($order);

            return null;
        }

        if (is_array(self::$pendingCheckoutLines) && self::$pendingCheckoutLines !== []) {
            $bookings = $this->syncCheckoutLines($order, self::$pendingCheckoutLines);
            self::$pendingCheckoutLines = null;

            return $bookings->first();
        }

        $bookings = collect();

        foreach ($virtualProducts as $orderProduct) {
            $bookings->push($this->upsertBookingForOrderProduct($order, $orderProduct, null));
        }

        $this->trashOrphanBookings($order, $virtualProducts->pluck('id')->map(fn ($id) => (int) $id)->all());

        return $bookings->first();
    }

    /**
     * @param  list<array{
     *     product_id: int,
     *     beautician_id: int|null,
     *     schedule_later?: bool,
     *     appointment_date?: string|null,
     *     appointment_time?: string|null,
     *     cart_item_id?: string|null
     * }>  $lines
     * @return Collection<int, TreatmentBooking>
     */
    public static function shouldDeferUntilPayment(Order|string $orderOrPaymentMethod): bool
    {
        $method = $orderOrPaymentMethod instanceof Order
            ? (string) $orderOrPaymentMethod->getRawOriginal('payment_method')
            : $orderOrPaymentMethod;

        return ! CheckoutCompletionGuard::isOfflineMethod($method);
    }

    public static function isSuppressingOrderObserverSync(): bool
    {
        return self::$suppressOrderObserverSync;
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function withoutOrderObserverSync(callable $callback): mixed
    {
        $previous = self::$suppressOrderObserverSync;
        self::$suppressOrderObserverSync = true;

        try {
            return $callback();
        } finally {
            self::$suppressOrderObserverSync = $previous;
        }
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public static function storePendingCheckoutLines(int $orderId, array $lines): void
    {
        if ($lines === []) {
            return;
        }

        Cache::put(
            self::PENDING_CHECKOUT_LINES_CACHE_PREFIX.$orderId,
            $lines,
            now()->addHours(self::PENDING_CHECKOUT_LINES_TTL_HOURS)
        );
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    public static function peekPendingCheckoutLines(int $orderId): ?array
    {
        $lines = Cache::get(self::PENDING_CHECKOUT_LINES_CACHE_PREFIX.$orderId);

        return is_array($lines) && $lines !== [] ? $lines : null;
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    public static function pullPendingCheckoutLines(int $orderId): ?array
    {
        $lines = self::peekPendingCheckoutLines($orderId);
        Cache::forget(self::PENDING_CHECKOUT_LINES_CACHE_PREFIX.$orderId);

        return $lines;
    }

    public static function forgetPendingCheckoutLines(int $orderId): void
    {
        Cache::forget(self::PENDING_CHECKOUT_LINES_CACHE_PREFIX.$orderId);
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    public static function resolvePendingCheckoutLines(Order $order): ?array
    {
        $cached = self::peekPendingCheckoutLines((int) $order->id);

        if ($cached !== null) {
            return $cached;
        }

        $stored = $order->checkout_treatment_lines;

        if (is_array($stored) && $stored !== []) {
            return $stored;
        }

        return app(self::class)->reconstructLinesFromOrderNote($order);
    }

    public function clearDeferredCheckoutLines(Order $order): void
    {
        self::forgetPendingCheckoutLines((int) $order->id);

        if ($order->checkout_treatment_lines !== null) {
            $order->forceFill(['checkout_treatment_lines' => null])->saveQuietly();
        }
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    public function reconstructLinesFromOrderNote(Order $order): ?array
    {
        $note = trim((string) $order->note);

        if ($note === '') {
            return null;
        }

        $order->loadMissing(['products.product']);
        $virtualProducts = $this->virtualOrderProducts($order)->values();

        if ($virtualProducts->isEmpty()) {
            return null;
        }

        $lines = [];

        foreach ($virtualProducts as $index => $orderProduct) {
            $n = $index + 1;

            if (! preg_match('/Treatment '.$n.' date:\s*([^\n]+)/i', $note, $dateMatch)) {
                continue;
            }

            if (! preg_match('/Treatment '.$n.' time:\s*([^\n]+)/i', $note, $timeMatch)) {
                continue;
            }

            $beauticianId = (int) ($order->beautician_id ?? 0);

            if (preg_match('/Treatment '.$n.' beautician:\s*([^\n]+)/i', $note, $beauticianMatch)) {
                $name = trim($beauticianMatch[1]);
                $found = Beautician::query()
                    ->whereRaw(
                        "TRIM(CONCAT(first_name, ' ', COALESCE(last_name, ''))) = ?",
                        [$name]
                    )
                    ->orWhereRaw(
                        "TRIM(CONCAT(first_name, ' ', COALESCE(last_name, ''))) LIKE ?",
                        [$name.'%']
                    )
                    ->value('id');

                if ($found) {
                    $beauticianId = (int) $found;
                }
            }

            try {
                $rawDate = trim($dateMatch[1]);
                $date = str_contains($rawDate, '/')
                    ? Carbon::createFromFormat('d/M/Y', $rawDate)->toDateString()
                    : Carbon::parse($rawDate)->toDateString();
            } catch (\Throwable) {
                continue;
            }

            $lines[] = [
                'cart_item_id' => null,
                'product_id' => (int) $orderProduct->product_id,
                'beautician_id' => $beauticianId ?: null,
                'schedule_later' => false,
                'appointment_date' => $date,
                'appointment_time' => trim($timeMatch[1]),
            ];
        }

        return $lines !== [] ? $lines : null;
    }

    /**
     * Create treatment bookings after online payment succeeds (lines were deferred at checkout).
     */

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public function assertPendingCheckoutLinesBookable(Order $order, array $lines): void
    {
        $spaBranchId = (int) ($order->spa_branch_id ?? 0);

        if ($spaBranchId < 1 || ! app('modules')->isEnabled('SpaBranch')) {
            return;
        }

        $pendingHolds = [];
        $orderId = (int) $order->id;

        foreach ($lines as $line) {
            if (
                ! is_array($line)
                || filter_var($line['schedule_later'] ?? false, FILTER_VALIDATE_BOOLEAN)
                || empty($line['beautician_id'])
                || empty($line['appointment_date'])
                || empty($line['appointment_time'])
            ) {
                continue;
            }

            $productId = (int) ($line['product_id'] ?? 0);
            $time = AppointmentTimeFormatter::to24Hour((string) $line['appointment_time'])
                ?? app(BeauticianAvailabilityService::class)->normalizeTime((string) $line['appointment_time']);

            if ($productId < 1 || $time === null) {
                throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
            }

            $this->availability->assertSlotBookable(
                $productId,
                $spaBranchId,
                (string) $line['appointment_date'],
                $time,
                (int) $line['beautician_id'],
                null,
                $orderId,
                $pendingHolds
            );

            $pendingHolds[] = [
                'beautician_id' => (int) $line['beautician_id'],
                'appointment_date' => (string) $line['appointment_date'],
                'appointment_time' => $time,
                'product_id' => $productId,
                'duration_minutes' => max(1, $this->availability->resolveDurationMinutes($productId, $spaBranchId)),
            ];
        }
    }

    public function syncPendingCheckoutLinesAfterPayment(Order $order): void
    {
        $order->loadMissing(['products.product']);

        $lines = self::resolvePendingCheckoutLines($order);

        if ($lines === null || $lines === []) {
            return;
        }

        $this->assertPendingCheckoutLinesBookable($order, $lines);
        $this->syncCheckoutLines($order, $lines);
        $this->clearDeferredCheckoutLines($order->fresh());
    }

    public function syncCheckoutLines(Order $order, array $lines): Collection
    {
        $order->loadMissing(['products.product']);

        $virtualProducts = $this->virtualOrderProducts($order)->values();
        $remaining = $virtualProducts->all();
        $bookings = collect();

        foreach ($lines as $line) {
            $productId = (int) ($line['product_id'] ?? 0);
            $orderProduct = null;

            foreach ($remaining as $index => $candidate) {
                if ((int) $candidate->product_id === $productId) {
                    $orderProduct = $candidate;
                    unset($remaining[$index]);
                    $remaining = array_values($remaining);
                    break;
                }
            }

            if (! $orderProduct) {
                continue;
            }

            $bookings->push($this->upsertBookingForOrderProduct($order, $orderProduct, $line));
        }

        foreach ($remaining as $orderProduct) {
            $bookings->push($this->upsertBookingForOrderProduct($order, $orderProduct, null));
        }

        $this->trashOrphanBookings(
            $order,
            $virtualProducts->pluck('id')->map(fn ($id) => (int) $id)->all()
        );
        $this->refreshOrderAppointmentSnapshot($order);

        return $bookings;
    }

    public function refreshOrderAppointmentSnapshot(Order $order): void
    {
        $primary = TreatmentBooking::query()
            ->where('order_id', $order->id)
            ->whereNotIn('status', [TreatmentBooking::STATUS_CANCELED])
            ->orderByRaw("CASE WHEN schedule_status = 'tba' THEN 1 ELSE 0 END")
            ->orderBy('appointment_date')
            ->orderBy('id')
            ->first();

        if (! $primary) {
            return;
        }

        self::$syncingFromOrder = true;

        try {
            $order->forceFill([
                'beautician_id' => $primary->beautician_id,
                'appointment_date' => $primary->appointment_date,
                'appointment_time' => $primary->appointment_time,
                'schedule_status' => $primary->schedule_status,
            ])->saveQuietly();
        } finally {
            self::$syncingFromOrder = false;
        }
    }

    public function syncAllOrders(): int
    {
        $count = 0;

        Order::query()
            ->where(function ($q) {
                $q->whereNotNull('beautician_id')
                    ->orWhere('schedule_status', 'tba')
                    ->orWhereHas('products.product', fn ($p) => $p->where('is_virtual', true));
            })
            ->with(['products.product'])
            ->orderBy('id')
            ->chunkById(100, function ($orders) use (&$count) {
                foreach ($orders as $order) {
                    if ($this->syncFromOrder($order)) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    public function cleanupInvalidBookings(): int
    {
        return TreatmentBooking::query()
            ->withTreatmentProduct(false)
            ->delete();
    }

    public function trashBookingsWithoutActiveOrder(): int
    {
        $count = 0;

        TreatmentBooking::query()
            ->whereNotNull('order_id')
            ->whereDoesntHave('order')
            ->each(function (TreatmentBooking $booking) use (&$count) {
                if (! $booking->trashed()) {
                    $booking->delete();
                    $count++;
                }
            });

        return $count;
    }

    public function trashBookingsForOrder(Order $order): void
    {
        if (! $order->id) {
            return;
        }

        TreatmentBooking::query()
            ->where('order_id', $order->id)
            ->each(fn (TreatmentBooking $booking) => $booking->delete());
    }

    public function restoreBookingsForOrder(Order $order): void
    {
        if (! $order->id) {
            return;
        }

        TreatmentBooking::onlyTrashed()
            ->where('order_id', $order->id)
            ->each(fn (TreatmentBooking $booking) => $booking->restore());
    }

    public function forceDeleteBookingsForOrder(Order $order): void
    {
        if (! $order->id) {
            return;
        }

        TreatmentBooking::withTrashed()
            ->where('order_id', $order->id)
            ->each(fn (TreatmentBooking $booking) => $booking->forceDelete());
    }

    /**
     * @param  array<string, mixed>|null  $line
     */
    private function upsertBookingForOrderProduct(Order $order, OrderProduct $orderProduct, ?array $line): TreatmentBooking
    {
        /** @var Product $product */
        $product = $orderProduct->product;

        $existing = TreatmentBooking::withTrashed()
            ->where('order_product_id', $orderProduct->id)
            ->first();

        if (! $existing) {
            $existing = TreatmentBooking::withTrashed()
                ->where('order_id', $order->id)
                ->where('product_id', $product->id)
                ->whereNull('order_product_id')
                ->first();
        }

        if ($existing?->trashed()) {
            $existing->restore();
        }

        $normalizedPhone = PhoneNumber::normalize($order->customer_phone);
        $scheduleLater = $line !== null
            ? filter_var($line['schedule_later'] ?? false, FILTER_VALIDATE_BOOLEAN)
            : ($order->schedule_status === 'tba');

        $beauticianId = $line['beautician_id'] ?? $order->beautician_id;
        $appointmentDate = $scheduleLater
            ? null
            : ($line['appointment_date'] ?? ($order->appointment_date?->format('Y-m-d') ?? $order->appointment_date));
        $appointmentTime = $scheduleLater
            ? null
            : ($line['appointment_time'] ?? $order->appointment_time);

        $lineTotal = is_object($orderProduct->line_total ?? null) && method_exists($orderProduct->line_total, 'amount')
            ? $orderProduct->line_total->amount()
            : (float) ($orderProduct->line_total ?? ((float) $orderProduct->unit_price * (int) $orderProduct->qty));

        $data = [
            'order_id' => $order->id,
            'order_product_id' => $orderProduct->id,
            'source' => TreatmentBooking::SOURCE_CHECKOUT,
            'beautician_id' => $beauticianId ? (int) $beauticianId : null,
            'spa_branch_id' => $order->spa_branch_id ?? null,
            'treatment_category_id' => $product->treatment_category_id,
            'product_id' => $product->id,
            'customer_first_name' => $order->customer_first_name,
            'customer_last_name' => $order->customer_last_name,
            'customer_phone' => $normalizedPhone !== '' ? $normalizedPhone : $order->customer_phone,
            'customer_email' => $order->customer_email,
            'total' => $lineTotal,
            'currency' => $order->currency,
            'notes' => $order->note,
            'payment_status' => $order->payment_status,
        ];

        if ($line !== null || ! $existing) {
            $data['appointment_date'] = $appointmentDate;
            $data['appointment_time'] = $appointmentTime ? AppointmentTimeFormatter::toStorage($appointmentTime) : null;
            $data['schedule_status'] = $scheduleLater ? TreatmentBooking::SCHEDULE_STATUS_TBA : null;
        }

        if (! $existing?->duration_minutes_snapshot && $product->id && $order->spa_branch_id) {
            $data['duration_minutes_snapshot'] = $this->availability->resolveDurationMinutes(
                (int) $product->id,
                (int) $order->spa_branch_id,
            );
        }

        if (! $existing) {
            $data['status'] = TreatmentBooking::statusFromOrder($order->status, $order->payment_status);
        } else {
            $fromOrder = TreatmentBooking::statusFromOrder($order->status, $order->payment_status);
            if ($fromOrder === TreatmentBooking::STATUS_CANCELED) {
                $data['status'] = TreatmentBooking::STATUS_CANCELED;
            }
        }

        self::$syncingFromOrder = true;

        try {
            if ($existing) {
                $existing->update($data);

                return $existing->fresh() ?? $existing;
            }

            return TreatmentBooking::create($data);
        } finally {
            self::$syncingFromOrder = false;
        }
    }

    /**
     * @return Collection<int, OrderProduct>
     */
    private function virtualOrderProducts(Order $order): Collection
    {
        return $order->products
            ->filter(fn (OrderProduct $op) => (bool) ($op->product?->is_virtual ?? false))
            ->values();
    }

    /**
     * @param  list<int>  $keepOrderProductIds
     */
    private function trashOrphanBookings(Order $order, array $keepOrderProductIds): void
    {
        TreatmentBooking::query()
            ->where('order_id', $order->id)
            ->get()
            ->each(function (TreatmentBooking $booking) use ($keepOrderProductIds) {
                $opid = $booking->order_product_id ? (int) $booking->order_product_id : null;

                if ($opid && in_array($opid, $keepOrderProductIds, true)) {
                    return;
                }

                $booking->delete();
            });
    }
}
