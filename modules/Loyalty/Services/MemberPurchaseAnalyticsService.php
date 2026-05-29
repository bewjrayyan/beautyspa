<?php

namespace Modules\Loyalty\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Order\Entities\Order;
use Modules\Support\Money;
use Modules\User\Entities\User;

class MemberPurchaseAnalyticsService
{
    private const MONTHS = 12;

    public function forCustomer(?User $user): array
    {
        if (! $user) {
            return $this->emptyPayload();
        }

        $orders = Order::query()
            ->where('customer_id', $user->id)
            ->where('status', '!=', Order::CANCELED)
            ->where('created_at', '>=', now()->subMonths(self::MONTHS - 1)->startOfMonth())
            ->orderBy('created_at')
            ->get();

        return $this->buildPayload($orders);
    }

    private function emptyPayload(): array
    {
        $labels = $this->monthLabels();

        $zeros = array_fill(0, count($labels), 0);

        return [
            'summary' => $this->emptySummary(),
            'charts' => [
                'currency' => currency_symbol(setting('default_currency')),
                'labels' => $labels,
                'treatments' => $zeros,
                'retail' => $zeros,
                'spend' => array_fill(0, count($labels), 0.0),
                'spend_formatted' => array_fill(0, count($labels), Money::inDefaultCurrency(0)->format()),
            ],
            'has_data' => false,
        ];
    }

    private function emptySummary(): array
    {
        return [
            'last_visit' => null,
            'days_since_last_visit' => null,
            'last_visit_hint_key' => 'loyalty::members.show.analytics_no_visit_yet',
            'last_visit_hint_params' => [],
            'next_appointment' => null,
            'next_appointment_hint_key' => 'loyalty::members.show.analytics_no_upcoming',
            'next_appointment_hint_params' => [],
            'visit_cadence_display' => null,
            'visit_cadence_hint_key' => 'loyalty::members.show.analytics_cadence_need_visits',
            'visit_cadence_hint_params' => [],
            'treatment_sessions' => 0,
            'treatment_spend' => Money::inDefaultCurrency(0)->format(),
            'retail_orders' => 0,
        ];
    }

    private function buildPayload(Collection $orders): array
    {
        $monthKeys = $this->monthKeys();
        $labels = $this->monthLabels();

        $treatments = array_fill(0, count($labels), 0);
        $retail = array_fill(0, count($labels), 0);
        $spendAmounts = array_fill(0, count($labels), 0.0);

        $treatmentOrders = $orders->filter(fn (Order $order) => $this->isTreatmentOrder($order));
        $retailOrders = $orders->reject(fn (Order $order) => $this->isTreatmentOrder($order));

        foreach ($orders as $order) {
            $index = $monthKeys->search($this->activityMonthKey($order));

            if ($index === false) {
                continue;
            }

            if ($this->isTreatmentOrder($order)) {
                $treatments[$index]++;
            } else {
                $retail[$index]++;
            }

            $spendAmounts[$index] += $order->total->amount();
        }

        $spendFormatted = array_map(
            fn (float $amount) => Money::inDefaultCurrency($amount)->format(),
            $spendAmounts
        );

        $today = now()->timezone(config('app.timezone'))->startOfDay();

        $pastTreatments = $treatmentOrders->filter(
            fn (Order $order) => $this->visitDate($order)->lte($today)
        );

        $upcomingTreatments = $treatmentOrders->filter(
            fn (Order $order) => $this->visitDate($order)->gt($today)
        );

        $lastVisitAt = $pastTreatments
            ->map(fn (Order $order) => $this->visitDate($order))
            ->sort()
            ->last();

        $nextVisitAt = $upcomingTreatments
            ->map(fn (Order $order) => $this->visitDate($order))
            ->sort()
            ->first();

        $daysSinceLastVisit = $lastVisitAt
            ? $lastVisitAt->diffInDays($today)
            : null;

        $cadenceDays = $this->medianDaysBetweenVisitDays($pastTreatments);

        $treatmentSpend = $treatmentOrders->sum(fn (Order $order) => $order->total->amount());

        return [
            'summary' => $this->buildSummary(
                $lastVisitAt,
                $daysSinceLastVisit,
                $nextVisitAt,
                $today,
                $cadenceDays,
                $pastTreatments,
                $treatmentOrders->count(),
                $treatmentSpend,
                $retailOrders->count(),
            ),
            'charts' => [
                'currency' => currency_symbol(setting('default_currency')),
                'labels' => $labels,
                'treatments' => $treatments,
                'retail' => $retail,
                'bookings' => $treatments,
                'purchases' => $retail,
                'spend' => $spendAmounts,
                'spend_formatted' => $spendFormatted,
            ],
            'has_data' => $orders->isNotEmpty(),
        ];
    }

    private function buildSummary(
        ?Carbon $lastVisitAt,
        ?int $daysSinceLastVisit,
        ?Carbon $nextVisitAt,
        Carbon $today,
        ?int $cadenceDays,
        Collection $pastTreatments,
        int $treatmentSessions,
        float $treatmentSpend,
        int $retailOrders,
    ): array {
        $summary = $this->emptySummary();

        $summary['treatment_sessions'] = $treatmentSessions;
        $summary['treatment_spend'] = Money::inDefaultCurrency($treatmentSpend)->format();
        $summary['retail_orders'] = $retailOrders;

        if ($lastVisitAt) {
            $summary['last_visit'] = $lastVisitAt->format('d M Y');
            $summary['days_since_last_visit'] = $daysSinceLastVisit;
            $summary['last_visit_hint_key'] = $daysSinceLastVisit === 0
                ? 'loyalty::members.show.analytics_last_visit_today'
                : 'loyalty::members.show.analytics_days_since';
            $summary['last_visit_hint_params'] = ['days' => number_format($daysSinceLastVisit)];

            if ($cadenceDays !== null && $daysSinceLastVisit !== null && $daysSinceLastVisit > $cadenceDays) {
                $summary['last_visit_hint_key'] = 'loyalty::members.show.analytics_follow_up_due';
                $summary['last_visit_hint_params'] = [
                    'days' => number_format($daysSinceLastVisit),
                    'cadence' => $this->formatCadenceLabel($cadenceDays),
                ];
            }
        }

        if ($nextVisitAt) {
            $daysUntil = $today->diffInDays($nextVisitAt, false);

            $summary['next_appointment'] = $nextVisitAt->format('d M Y');
            $summary['next_appointment_hint_key'] = $daysUntil === 0
                ? 'loyalty::members.show.analytics_next_visit_today'
                : 'loyalty::members.show.analytics_next_visit_in';
            $summary['next_appointment_hint_params'] = ['days' => number_format(max(0, $daysUntil))];
        }

        if ($cadenceDays !== null) {
            $summary['visit_cadence_display'] = $this->formatCadenceLabel($cadenceDays);
            $summary['visit_cadence_hint_key'] = 'loyalty::members.show.analytics_cadence_hint';
            $summary['visit_cadence_hint_params'] = [
                'count' => number_format($this->uniqueVisitDayCount($pastTreatments)),
            ];
        }

        return $summary;
    }

    private function monthKeys(): Collection
    {
        return collect(range(self::MONTHS - 1, 0))
            ->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo)->format('Y-m'));
    }

    private function monthLabels(): array
    {
        return $this->monthKeys()
            ->map(fn (string $ym) => Carbon::createFromFormat('Y-m', $ym)->format('M Y'))
            ->values()
            ->all();
    }

    /**
     * Treatment = scheduled session (beautician and/or appointment date).
     */
    private function isTreatmentOrder(Order $order): bool
    {
        return (bool) $order->beautician_id || $order->appointment_date;
    }

    /**
     * Clinical visit day for cadence (appointment date when set, otherwise order date).
     */
    private function visitDate(Order $order): Carbon
    {
        $date = $order->appointment_date ?? $order->created_at;

        return Carbon::parse($date)->timezone(config('app.timezone'))->startOfDay();
    }

    /**
     * Bucket monthly charts by appointment month when available.
     */
    private function activityMonthKey(Order $order): string
    {
        $date = $order->appointment_date ?? $order->created_at;

        return Carbon::parse($date)->format('Y-m');
    }

    private function uniqueVisitDayCount(Collection $treatmentOrders): int
    {
        return $treatmentOrders
            ->map(fn (Order $order) => $this->visitDate($order)->format('Y-m-d'))
            ->unique()
            ->count();
    }

    /**
     * Median gap between distinct visit days (ignores same-day sessions).
     */
    private function medianDaysBetweenVisitDays(Collection $treatmentOrders): ?int
    {
        $uniqueDates = $treatmentOrders
            ->map(fn (Order $order) => $this->visitDate($order)->format('Y-m-d'))
            ->unique()
            ->sort()
            ->values();

        if ($uniqueDates->count() < 2) {
            return null;
        }

        $gaps = [];

        for ($i = 1; $i < $uniqueDates->count(); $i++) {
            $gap = Carbon::parse($uniqueDates[$i - 1])
                ->diffInDays(Carbon::parse($uniqueDates[$i]));

            if ($gap > 0) {
                $gaps[] = $gap;
            }
        }

        if ($gaps === []) {
            return null;
        }

        sort($gaps);

        $middle = (int) floor(count($gaps) / 2);

        if (count($gaps) % 2 === 0) {
            return (int) round(($gaps[$middle - 1] + $gaps[$middle]) / 2);
        }

        return $gaps[$middle];
    }

    private function formatCadenceLabel(int $days): string
    {
        if ($days >= 14 && $days % 7 === 0) {
            $weeks = (int) ($days / 7);

            return trans('loyalty::members.show.analytics_cadence_weeks', ['weeks' => $weeks]);
        }

        if ($days >= 28) {
            return trans('loyalty::members.show.analytics_cadence_weeks', [
                'weeks' => (int) round($days / 7),
            ]);
        }

        return trans('loyalty::members.show.analytics_days_value', ['days' => number_format($days)]);
    }
}
