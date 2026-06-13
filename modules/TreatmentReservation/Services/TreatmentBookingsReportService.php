<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Support\Money;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TreatmentBookingsReportService
{
    public function baseQuery(
        ?string $from = null,
        ?string $to = null,
        ?int $beauticianId = null,
        ?int $categoryId = null,
        ?string $source = null
    ): Builder {
        $query = TreatmentBooking::query()
            ->withTreatmentProduct()
            ->with(['beautician', 'category', 'product'])
            ->whereNotNull('appointment_date')
            ->when($from, fn (Builder $q) => $q->whereDate('appointment_date', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('appointment_date', '<=', $to))
            ->when($beauticianId, fn (Builder $q) => $q->where('beautician_id', $beauticianId))
            ->when($categoryId, fn (Builder $q) => $q->where('treatment_category_id', $categoryId))
            ->when($source === 'manual', fn (Builder $q) => $q->whereIn('source', TreatmentBooking::manualSources()))
            ->when($source === 'checkout', fn (Builder $q) => $q->where(function (Builder $inner) {
                $inner->whereNull('source')
                    ->orWhere('source', TreatmentBooking::SOURCE_CHECKOUT);
            }))
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');

        return $query;
    }


    /**
     * @return array{
     *     total: int,
     *     pending: int,
     *     inProgress: int,
     *     completed: int,
     *     canceled: int,
     *     revenue: Money
     * }
     */
    public function summary(
        ?string $from = null,
        ?string $to = null,
        ?int $beauticianId = null,
        ?int $categoryId = null,
        ?string $source = null
    ): array {
        $base = $this->baseQuery($from, $to, $beauticianId, $categoryId, $source);

        return [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', TreatmentBooking::STATUS_PENDING)->count(),
            'inProgress' => (clone $base)->where('status', TreatmentBooking::STATUS_IN_PROGRESS)->count(),
            'completed' => (clone $base)->where('status', TreatmentBooking::STATUS_COMPLETED)->count(),
            'canceled' => (clone $base)->where('status', TreatmentBooking::STATUS_CANCELED)->count(),
            'revenue' => Money::inDefaultCurrency(
                (clone $base)
                    ->where('status', TreatmentBooking::STATUS_COMPLETED)
                    ->sum('total')
            ),
        ];
    }


    public function exportCsv(
        ?string $from = null,
        ?string $to = null,
        ?int $beauticianId = null,
        ?int $categoryId = null,
        ?string $source = null
    ): StreamedResponse {
        $filename = 'treatment-bookings-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($from, $to, $beauticianId, $categoryId, $source) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Order ID',
                'Source',
                'Customer',
                'Phone',
                'Email',
                'Treatment',
                'Category',
                'Beautician',
                'Appointment Date',
                'Appointment Time',
                'Status',
                'Total',
                'Currency',
            ]);

            $this->baseQuery($from, $to, $beauticianId, $categoryId, $source)
                ->chunkById(100, function ($bookings) use ($handle) {
                    foreach ($bookings as $booking) {
                        fputcsv($handle, [
                            $booking->id,
                            $booking->order_id,
                            $booking->source ?? TreatmentBooking::SOURCE_CHECKOUT,
                            $booking->customer_full_name,
                            $booking->customer_phone,
                            $booking->customer_email,
                            $booking->product?->name,
                            $booking->category?->name,
                            $booking->beautician?->name,
                            $booking->appointment_date?->format('Y-m-d'),
                            $booking->appointment_time,
                            $booking->status,
                            $booking->total,
                            $booking->currency,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }


    public function bookings(
        ?string $from = null,
        ?string $to = null,
        ?int $beauticianId = null,
        ?int $categoryId = null,
        ?string $source = null
    ): Collection {
        return $this->baseQuery($from, $to, $beauticianId, $categoryId, $source)->get();
    }


    /**
     * @return Collection<int, array{beautician_id: int|null, beautician_name: string, completed: int, revenue: float}>
     */
    public function beauticianBreakdown(
        ?string $from = null,
        ?string $to = null,
        ?int $beauticianId = null,
        ?int $categoryId = null,
        ?string $source = null
    ): Collection {
        return $this->baseQuery($from, $to, $beauticianId, $categoryId, $source)
            ->where('status', TreatmentBooking::STATUS_COMPLETED)
            ->get()
            ->groupBy('beautician_id')
            ->map(function ($bookings, $id) {
                $beautician = $bookings->first()?->beautician;

                return [
                    'beautician_id' => $id ? (int) $id : null,
                    'beautician_name' => $beautician?->name ?? trans('treatmentreservation::admin.reports.unassigned'),
                    'completed' => $bookings->count(),
                    'revenue' => $bookings->sum('total'),
                ];
            })
            ->sortByDesc('revenue')
            ->values();
    }


    public static function defaultDateRange(): array
    {
        $start = Carbon::now()->startOfMonth()->toDateString();
        $end = Carbon::now()->endOfMonth()->toDateString();

        return ['from' => $start, 'to' => $end];
    }
}
