<?php

namespace Modules\TreatmentReservation\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;

class UpcomingJobUrgencyService
{
    private const LOOKBACK_DAYS = 14;

    private const LOOKAHEAD_DAYS = 1;

    public function emptyPayload(): array
    {
        return [
            'has_alerts' => false,
            'highest_urgency' => null,
            'critical_count' => 0,
            'warning_count' => 0,
            'info_count' => 0,
            'headline' => null,
            'lead' => null,
            'items' => [],
            'action_url' => null,
            'action_label' => null,
            'scope' => 'team',
        ];
    }

    public function forAdminTeam(): array
    {
        return $this->buildPayload(
            $this->actionableBookings(),
            route('admin.treatment_reservations.index', ['view' => 'kanban']),
            TrLang::trans('admin.urgency.open_job_sheet'),
            'team',
            TrLang::trans('admin.urgency.team_headline'),
            TrLang::trans('admin.urgency.team_lead'),
        );
    }

    public function forBeautician(int $beauticianId): array
    {
        return $this->buildPayload(
            $this->actionableBookings($beauticianId),
            route('admin.treatment_reservations.portal', ['view' => 'kanban']),
            TrLang::trans('admin.urgency.open_my_jobs'),
            'beautician',
            TrLang::trans('admin.urgency.beautician_headline'),
            TrLang::trans('admin.urgency.beautician_lead'),
        );
    }

    private function actionableBookings(?int $beauticianId = null): Collection
    {
        $today = now()->timezone(config('app.timezone'))->startOfDay();
        $windowStart = $today->copy()->subDays(self::LOOKBACK_DAYS);
        $windowEnd = $today->copy()->addDays(self::LOOKAHEAD_DAYS);

        return TreatmentBooking::query()
            ->withActiveOrder()
            ->withTreatmentProduct()
            ->with(['beautician', 'product', 'category'])
            ->whereIn('status', [
                TreatmentBooking::STATUS_PENDING,
                TreatmentBooking::STATUS_IN_PROGRESS,
            ])
            ->when($beauticianId, fn ($query) => $query->where('beautician_id', $beauticianId))
            ->whereNotNull('appointment_date')
            ->where(function ($query) use ($windowStart, $windowEnd, $today) {
                $query->whereBetween('appointment_date', [
                    $windowStart->toDateString(),
                    $windowEnd->toDateString(),
                ])->orWhere(function ($overdue) use ($today) {
                    $overdue->whereDate('appointment_date', '<', $today)
                        ->where('status', TreatmentBooking::STATUS_PENDING);
                });
            })
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();
    }

    private function buildPayload(
        Collection $bookings,
        string $actionUrl,
        string $actionLabel,
        string $scope,
        string $headlineTemplate,
        string $lead,
    ): array {
        if ($bookings->isEmpty()) {
            return $this->emptyPayload();
        }

        $now = now()->timezone(config('app.timezone'));

        $items = $bookings
            ->map(fn (TreatmentBooking $booking) => $this->mapItem($booking, $now, $scope))
            ->sortBy([
                fn (array $item) => match ($item['urgency']) {
                    'critical' => 0,
                    'warning' => 1,
                    default => 2,
                },
                fn (array $item) => $item['sort_key'],
            ])
            ->values();

        $criticalCount = $items->where('urgency', 'critical')->count();
        $warningCount = $items->where('urgency', 'warning')->count();
        $infoCount = $items->where('urgency', 'info')->count();

        $highest = $criticalCount > 0 ? 'critical' : ($warningCount > 0 ? 'warning' : 'info');

        return [
            'has_alerts' => true,
            'highest_urgency' => $highest,
            'critical_count' => $criticalCount,
            'warning_count' => $warningCount,
            'info_count' => $infoCount,
            'headline' => $headlineTemplate,
            'lead' => $lead,
            'items' => $items->take(8)->all(),
            'action_url' => $actionUrl,
            'action_label' => $actionLabel,
            'scope' => $scope,
        ];
    }

    private function mapItem(TreatmentBooking $booking, Carbon $now, string $scope): array
    {
        $startsAt = $this->appointmentStartsAt($booking);
        $urgency = $this->resolveUrgency($booking, $startsAt, $now);
        $minutesUntil = $startsAt ? $now->diffInMinutes($startsAt, false) : null;

        $messageKey = match (true) {
            $urgency === 'critical' && $minutesUntil !== null && $minutesUntil < 0 => 'treatmentreservation::admin.urgency.message_overdue',
            $urgency === 'critical' => 'treatmentreservation::admin.urgency.message_starting_soon',
            $urgency === 'warning' && $booking->status === TreatmentBooking::STATUS_IN_PROGRESS => 'treatmentreservation::admin.urgency.message_in_progress',
            $urgency === 'warning' => 'treatmentreservation::admin.urgency.message_upcoming',
            default => 'treatmentreservation::admin.urgency.message_scheduled',
        };

        return [
            'id' => $booking->id,
            'urgency' => $urgency,
            'urgency_label' => TrLang::trans("admin.urgency.level_{$urgency}"),
            'message' => TrLang::trans(str_replace('treatmentreservation::', '', $messageKey), [
                'time' => $this->formatRelativeTime($minutesUntil),
                'when' => $this->formatWhenLabel($booking, $startsAt, $now),
            ]),
            'customer_name' => $booking->customer_full_name ?: '—',
            'treatment_name' => $booking->product?->name ?? '—',
            'beautician_name' => $booking->beautician?->name,
            'status' => $booking->status,
            'status_label' => TrLang::trans("admin.kanban.{$booking->status}"),
            'time_display' => $this->formatTimeDisplay($booking),
            'date_display' => $this->formatDateDisplay($booking, $now),
            'sort_key' => $startsAt?->timestamp ?? 0,
            'show_beautician' => $scope === 'team',
            'order_url' => $booking->order_id
                ? route('admin.orders.show', $booking->order_id)
                : null,
        ];
    }

    private function resolveUrgency(
        TreatmentBooking $booking,
        ?Carbon $startsAt,
        Carbon $now,
    ): string {
        if ($booking->status === TreatmentBooking::STATUS_IN_PROGRESS) {
            if ($startsAt && $now->diffInMinutes($startsAt, false) < -180) {
                return 'warning';
            }

            return 'info';
        }

        if (! $startsAt) {
            return $booking->appointment_date?->isPast() ? 'critical' : 'info';
        }

        $minutesUntil = $now->diffInMinutes($startsAt, false);

        if ($minutesUntil < 0) {
            return 'critical';
        }

        if ($minutesUntil <= 30) {
            return 'critical';
        }

        if ($minutesUntil <= 120) {
            return 'warning';
        }

        return 'info';
    }

    private function appointmentStartsAt(TreatmentBooking $booking): ?Carbon
    {
        if (! $booking->appointment_date) {
            return null;
        }

        $startsAt = Carbon::parse($booking->appointment_date)
            ->timezone(config('app.timezone'))
            ->startOfDay();

        if (filled($booking->appointment_time)) {
            $parts = explode(':', substr((string) $booking->appointment_time, 0, 5));

            return $startsAt->setTime((int) ($parts[0] ?? 0), (int) ($parts[1] ?? 0));
        }

        return $startsAt->setTime(9, 0);
    }

    private function formatRelativeTime(?int $minutesUntil): string
    {
        if ($minutesUntil === null) {
            return '—';
        }

        if ($minutesUntil < 0) {
            $overdue = abs($minutesUntil);

            if ($overdue < 60) {
                return TrLang::trans('admin.urgency.overdue_minutes', ['count' => $overdue]);
            }

            return TrLang::trans('admin.urgency.overdue_hours', [
                'count' => (int) max(1, round($overdue / 60)),
            ]);
        }

        if ($minutesUntil < 60) {
            return TrLang::trans('admin.urgency.in_minutes', ['count' => $minutesUntil]);
        }

        return TrLang::trans('admin.urgency.in_hours', [
            'count' => (int) max(1, round($minutesUntil / 60)),
        ]);
    }

    private function formatWhenLabel(TreatmentBooking $booking, ?Carbon $startsAt, Carbon $now): string
    {
        if (! $startsAt) {
            return $booking->appointment_date?->format('d M Y') ?? '—';
        }

        if ($startsAt->isToday()) {
            return TrLang::trans('admin.urgency.today_at', [
                'time' => $this->formatTimeDisplay($booking),
            ]);
        }

        if ($startsAt->isTomorrow()) {
            return TrLang::trans('admin.urgency.tomorrow_at', [
                'time' => $this->formatTimeDisplay($booking),
            ]);
        }

        return $startsAt->format('d M Y') . ' · ' . $this->formatTimeDisplay($booking);
    }

    private function formatTimeDisplay(TreatmentBooking $booking): string
    {
        if (! filled($booking->appointment_time)) {
            return TrLang::trans('admin.urgency.time_tbc');
        }

        return substr((string) $booking->appointment_time, 0, 5);
    }

    private function formatDateDisplay(TreatmentBooking $booking, Carbon $now): string
    {
        if (! $booking->appointment_date) {
            return '—';
        }

        $date = Carbon::parse($booking->appointment_date)->timezone(config('app.timezone'));

        if ($date->isToday()) {
            return TrLang::trans('admin.urgency.today');
        }

        if ($date->isTomorrow()) {
            return TrLang::trans('admin.urgency.tomorrow');
        }

        if ($date->isPast()) {
            return TrLang::trans('admin.urgency.overdue_date', [
                'date' => $date->format('d M'),
            ]);
        }

        return $date->format('d M Y');
    }
}
