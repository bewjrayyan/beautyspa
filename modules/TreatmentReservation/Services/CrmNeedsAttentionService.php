<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Support\AppointmentTimeFormatter;
use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;

class CrmNeedsAttentionService
{
    private const ITEM_LIMIT = 4;

    private const LIST_LIMIT = 10;

    /** CRM reminder queue horizon (days), inclusive of today. */
    private const REMINDER_HORIZON_DAYS = 7;

    /**
     * Actionable CRM queues — overdue, unassigned, TBA, reminders, unpaid.
     *
     * @return array{total: int, buckets: list<array<string, mixed>>, items: list<array<string, mixed>>}
     */
    public function forDashboard(
        ?int $beauticianId = null,
        ?int $categoryId = null,
        ?int $spaBranchId = null,
    ): array {
        $today = today();
        // Inclusive window: today .. today+(N-1) = N calendar days.
        $reminderUntil = $today->copy()->addDays(self::REMINDER_HORIZON_DAYS - 1);

        $buckets = [
            $this->bucket('overdue', 'critical', $this->overdueQuery($beauticianId, $categoryId, $spaBranchId, $today)),
            $this->bucket('unassigned', 'warning', $this->unassignedQuery($beauticianId, $categoryId, $spaBranchId)),
            $this->bucket('tba', 'warning', $this->tbaQuery($beauticianId, $categoryId, $spaBranchId)),
            $this->bucket('reminder', 'info', $this->reminderQuery($beauticianId, $categoryId, $spaBranchId, $today, $reminderUntil)),
            $this->bucket('payment', 'warning', $this->paymentQuery($beauticianId, $categoryId, $spaBranchId)),
        ];

        $uniqueIds = collect($buckets)
            ->flatMap(fn (array $bucket) => $bucket['member_ids'] ?? [])
            ->unique()
            ->values();

        $items = collect($buckets)
            ->flatMap(fn (array $bucket) => $bucket['items'])
            ->unique('id')
            ->sortBy([
                fn (array $item) => match ($item['urgency']) {
                    'critical' => 0,
                    'warning' => 1,
                    default => 2,
                },
                fn (array $item) => $item['sort_key'],
            ])
            ->take(self::LIST_LIMIT)
            ->values()
            ->all();

        return [
            'total' => $uniqueIds->count(),
            'buckets' => array_map(static function (array $bucket): array {
                unset($bucket['member_ids']);

                return $bucket;
            }, $buckets),
            'items' => $items,
        ];
    }

    /**
     * @param  Builder<TreatmentBooking>  $query
     * @return array<string, mixed>
     */
    private function bucket(string $key, string $urgency, Builder $query): array
    {
        $memberIds = (clone $query)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $count = count($memberIds);
        $bookings = (clone $query)
            ->with(['product', 'beautician', 'order'])
            ->limit(self::ITEM_LIMIT)
            ->get();

        return [
            'key' => $key,
            'urgency' => $urgency,
            'count' => $count,
            'member_ids' => $memberIds,
            'label' => TrLang::trans('admin.crm.needs_attention_bucket_'.$key),
            'hint' => TrLang::trans('admin.crm.needs_attention_bucket_'.$key.'_hint'),
            'items' => $bookings
                ->map(fn (TreatmentBooking $booking) => $this->serializeItem($booking, $key, $urgency))
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeItem(TreatmentBooking $booking, string $reason, string $urgency): array
    {
        $treatmentLine = $booking->treatmentLineMeta();

        return [
            'id' => $booking->id,
            'reason' => $reason,
            'urgency' => $urgency,
            'reason_label' => TrLang::trans('admin.crm.needs_attention_bucket_'.$reason),
            'customer_name' => $booking->customer_full_name ?: TrLang::trans('admin.crm.ledger_unknown_client'),
            'treatment_name' => $treatmentLine['product_name']
                ?? $booking->product?->name
                ?? TrLang::trans('admin.crm.ledger_unknown_treatment'),
            'beautician_name' => $booking->beautician?->full_name ?: $booking->beautician?->name,
            'appointment_label' => $this->appointmentLabel($booking),
            'status' => $booking->status,
            'can_open_detail' => true,
            'sort_key' => ($booking->appointment_date?->format('Y-m-d') ?? '9999-99-99')
                .'|'.($booking->appointment_time ?? '')
                .'|'.$booking->id,
        ];
    }

    private function appointmentLabel(TreatmentBooking $booking): string
    {
        if ($booking->isTbaSchedule()) {
            return TrLang::trans('admin.tba.badge');
        }

        $date = $booking->appointment_date
            ? $booking->appointment_date->format('j M Y')
            : TrLang::trans('admin.crm.ledger_unscheduled');

        $time = filled($booking->appointment_time)
            ? (AppointmentTimeFormatter::toDisplay($booking->appointment_time) ?: $booking->appointment_time)
            : TrLang::trans('admin.crm.ledger_time_tbc');

        return $date.' · '.$time;
    }

    /**
     * @return Builder<TreatmentBooking>
     */
    private function baseQuery(?int $beauticianId, ?int $categoryId, ?int $spaBranchId): Builder
    {
        return TreatmentBooking::query()
            ->withActiveOrder()
            ->withTreatmentProduct()
            ->whereNot('status', TreatmentBooking::STATUS_CANCELED)
            ->when($beauticianId, fn (Builder $query) => $query->where('beautician_id', $beauticianId))
            ->when($categoryId, fn (Builder $query) => $query->where('treatment_category_id', $categoryId))
            ->when(
                $spaBranchId && is_module_enabled('SpaBranch'),
                function (Builder $query) use ($spaBranchId): void {
                    // Prefer booking.spa_branch_id so unassigned rows still match a branch filter.
                    $query->where(function (Builder $inner) use ($spaBranchId): void {
                        $inner->where('spa_branch_id', $spaBranchId)
                            ->orWhereHas(
                                'beautician.spaBranches',
                                fn (Builder $branchQuery) => $branchQuery->where('spa_branches.id', $spaBranchId)
                            );
                    });
                }
            );
    }

    /**
     * @return Builder<TreatmentBooking>
     */
    private function overdueQuery(?int $beauticianId, ?int $categoryId, ?int $spaBranchId, Carbon $today): Builder
    {
        return $this->baseQuery($beauticianId, $categoryId, $spaBranchId)
            ->where('status', TreatmentBooking::STATUS_PENDING)
            ->whereNotNull('appointment_date')
            ->whereDate('appointment_date', '<', $today)
            // TBA belongs in the TBA bucket, not overdue.
            ->where(function (Builder $query): void {
                $query->whereNull('schedule_status')
                    ->orWhere('schedule_status', '!=', TreatmentBooking::SCHEDULE_STATUS_TBA);
            })
            ->whereNotNull('appointment_time')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');
    }

    /**
     * @return Builder<TreatmentBooking>
     */
    private function unassignedQuery(?int $beauticianId, ?int $categoryId, ?int $spaBranchId): Builder
    {
        if ($beauticianId) {
            return $this->baseQuery($beauticianId, $categoryId, $spaBranchId)->whereRaw('1 = 0');
        }

        return $this->baseQuery(null, $categoryId, $spaBranchId)
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ])
            ->whereNull('beautician_id')
            ->orderByRaw('appointment_date is null')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');
    }

    /**
     * @return Builder<TreatmentBooking>
     */
    private function tbaQuery(?int $beauticianId, ?int $categoryId, ?int $spaBranchId): Builder
    {
        return $this->baseQuery($beauticianId, $categoryId, $spaBranchId)
            ->tbaSchedule()
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ])
            ->orderByDesc('id');
    }

    /**
     * @return Builder<TreatmentBooking>
     */
    private function reminderQuery(
        ?int $beauticianId,
        ?int $categoryId,
        ?int $spaBranchId,
        Carbon $today,
        Carbon $until,
    ): Builder {
        return $this->baseQuery($beauticianId, $categoryId, $spaBranchId)
            ->where('status', TreatmentBooking::STATUS_PENDING)
            ->whereNull('customer_reminder_sent_at')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->whereNotNull('appointment_date')
            ->whereNotNull('appointment_time')
            ->where('appointment_time', '!=', '')
            // TBA belongs in the TBA bucket, even if a provisional date/time exists.
            ->where(function (Builder $query): void {
                $query->whereNull('schedule_status')
                    ->orWhere('schedule_status', '!=', TreatmentBooking::SCHEDULE_STATUS_TBA);
            })
            ->whereBetween('appointment_date', [$today->toDateString(), $until->toDateString()])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');
    }

    /**
     * Outstanding payment aligned with TreatmentBooking::hasOutstandingPayment().
     *
     * @return Builder<TreatmentBooking>
     */
    private function paymentQuery(?int $beauticianId, ?int $categoryId, ?int $spaBranchId): Builder
    {
        return $this->baseQuery($beauticianId, $categoryId, $spaBranchId)
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ])
            ->where(function (Builder $query): void {
                $query->where(function (Builder $manual): void {
                    $manual->whereIn('source', [
                        TreatmentBooking::SOURCE_ADMIN_MANUAL,
                        TreatmentBooking::SOURCE_PORTAL_MANUAL,
                    ])->where(function (Builder $payment): void {
                        $payment->whereNull('payment_status')
                            ->orWhere('payment_status', '!=', TreatmentBooking::PAYMENT_FULL_PAID);
                    });
                })->orWhere(function (Builder $checkout): void {
                    $checkout->where(function (Builder $source): void {
                        $source->whereNull('source')
                            ->orWhere('source', TreatmentBooking::SOURCE_CHECKOUT);
                    })->whereHas('order', function (Builder $order): void {
                        $order->where(function (Builder $payment): void {
                            $payment->whereNull('payment_status')
                                ->orWhereNotIn('payment_status', [Order::PAYMENT_PAID]);
                        });
                    });
                });
            })
            ->orderByRaw('appointment_date is null')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');
    }
}
