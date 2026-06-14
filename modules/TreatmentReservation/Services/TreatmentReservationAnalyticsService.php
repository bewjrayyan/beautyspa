<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Carbon;
use Modules\Beautician\Entities\Beautician;
use Modules\Support\Money;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class TreatmentReservationAnalyticsService
{
  public const DEFAULT_DAYS = 30;


  /**
   * @return array{
   *     periodDays: int,
   *     totalBookings: int,
   *     completed: int,
   *     canceled: int,
   *     completionRate: float,
   *     conversionRate: float,
   *     noShowRate: float,
   *     noShows: int,
   *     revenue: Money,
   *     revenueFormatted: string
   * }
   */
  public function overview(int $days = self::DEFAULT_DAYS): array
  {
    $from = Carbon::now()->subDays($days - 1)->startOfDay()->toDateString();
    $to = Carbon::now()->endOfDay()->toDateString();

    $base = $this->periodBase($from, $to);
    $total = (clone $base)->count();
    $canceled = (clone $base)->where('status', TreatmentBooking::STATUS_CANCELED)->count();
    $completed = (clone $base)->where('status', TreatmentBooking::STATUS_COMPLETED)->count();
    $activeTotal = max($total - $canceled, 0);

    $pastBase = TreatmentBooking::query()
      ->withTreatmentProduct()
      ->whereDate('appointment_date', '<', today())
      ->whereNot('status', TreatmentBooking::STATUS_CANCELED);

    $pastTotal = (clone $pastBase)->count();
    $noShows = (clone $pastBase)
      ->whereIn('status', [
        TreatmentBooking::STATUS_PENDING,
        TreatmentBooking::STATUS_IN_PROGRESS,
      ])
      ->count();

    $pastCompleted = (clone $pastBase)
      ->where('status', TreatmentBooking::STATUS_COMPLETED)
      ->count();

    $revenue = Money::inDefaultCurrency(
      (clone $base)
        ->where('status', TreatmentBooking::STATUS_COMPLETED)
        ->sum('total')
    );

    return [
      'periodDays' => $days,
      'totalBookings' => $total,
      'completed' => $completed,
      'canceled' => $canceled,
      'completionRate' => $activeTotal > 0 ? round(($completed / $activeTotal) * 100, 1) : 0.0,
      'conversionRate' => $pastTotal > 0 ? round(($pastCompleted / $pastTotal) * 100, 1) : 0.0,
      'noShowRate' => $pastTotal > 0 ? round(($noShows / $pastTotal) * 100, 1) : 0.0,
      'noShows' => $noShows,
      'revenue' => $revenue,
      'revenueFormatted' => $revenue->format(),
    ];
  }


  /**
   * @return array{
   *     labels: array<int, string>,
   *     amounts: array<int, float>,
   *     formatted: array<int, string>,
   *     currency: string
   * }
   */
  public function revenueTrend(int $days = self::DEFAULT_DAYS): array
  {
    $labels = [];
    $amounts = [];
    $formatted = [];

    for ($i = $days - 1; $i >= 0; $i--) {
      $date = today()->subDays($i);
      $sum = (float) TreatmentBooking::query()
        ->withTreatmentProduct()
        ->where('status', TreatmentBooking::STATUS_COMPLETED)
        ->whereDate('appointment_date', $date)
        ->sum('total');

      $labels[] = $date->format('M j');
      $amounts[] = $sum;
      $formatted[] = Money::inDefaultCurrency($sum)->format();
    }

    return [
      'labels' => $labels,
      'amounts' => $amounts,
      'formatted' => $formatted,
      'currency' => setting('default_currency'),
    ];
  }


  /**
   * @return array{labels: array<int, string>, counts: array<int, int>}
   */
  public function statusBreakdown(int $days = self::DEFAULT_DAYS): array
  {
    $from = Carbon::now()->subDays($days - 1)->startOfDay()->toDateString();
    $to = Carbon::now()->endOfDay()->toDateString();
    $base = $this->periodBase($from, $to);

    return [
      'labels' => [
        trans('treatmentreservation::admin.kanban.pending'),
        trans('treatmentreservation::admin.kanban.in_progress'),
        trans('treatmentreservation::admin.kanban.completed'),
        trans('treatmentreservation::admin.reports.canceled'),
      ],
      'counts' => [
        (clone $base)->where('status', TreatmentBooking::STATUS_PENDING)->count(),
        (clone $base)->where('status', TreatmentBooking::STATUS_IN_PROGRESS)->count(),
        (clone $base)->where('status', TreatmentBooking::STATUS_COMPLETED)->count(),
        (clone $base)->where('status', TreatmentBooking::STATUS_CANCELED)->count(),
      ],
    ];
  }


  /**
   * @return array{
   *     title: string,
   *     metric: string,
   *     labels: array<int, string>,
   *     amounts: array<int, float>,
   *     formatted: array<int, string>,
   *     bookingCounts: array<int, int>,
   *     currency: string
   * }
   */
  public function revenueByBeautician(int $days = self::DEFAULT_DAYS, int $limit = 5): array
  {
    $from = Carbon::now()->subDays($days - 1)->startOfDay()->toDateString();
    $to = Carbon::now()->endOfDay()->toDateString();

    $bookingRows = TreatmentBooking::query()
      ->withTreatmentProduct()
      ->selectRaw('beautician_id, COUNT(*) as booking_total')
      ->whereDate('appointment_date', '>=', $from)
      ->whereDate('appointment_date', '<=', $to)
      ->whereNot('status', TreatmentBooking::STATUS_CANCELED)
      ->whereNotNull('beautician_id')
      ->groupBy('beautician_id')
      ->get()
      ->keyBy('beautician_id');

    $revenueRows = TreatmentBooking::query()
      ->withTreatmentProduct()
      ->selectRaw('beautician_id, SUM(total) as revenue_total')
      ->where('status', TreatmentBooking::STATUS_COMPLETED)
      ->whereDate('appointment_date', '>=', $from)
      ->whereDate('appointment_date', '<=', $to)
      ->whereNotNull('beautician_id')
      ->groupBy('beautician_id')
      ->get()
      ->keyBy('beautician_id');

    $beauticianIds = $bookingRows->keys()
      ->merge($revenueRows->keys())
      ->unique()
      ->values();

    if ($beauticianIds->isEmpty()) {
      return [
        'title' => trans('treatmentreservation::admin.analytics.revenue_by_beautician'),
        'metric' => 'revenue',
        'labels' => [],
        'amounts' => [],
        'formatted' => [],
        'bookingCounts' => [],
        'currency' => setting('default_currency'),
      ];
    }

    $beauticians = Beautician::query()
      ->whereIn('id', $beauticianIds)
      ->orderBy('first_name')
      ->orderBy('last_name')
      ->get(['id', 'first_name', 'last_name'])
      ->mapWithKeys(fn (Beautician $beautician) => [$beautician->id => $beautician->name]);

    $rows = $beauticianIds
      ->map(function ($beauticianId) use ($bookingRows, $revenueRows) {
        return [
          'beautician_id' => (int) $beauticianId,
          'booking_total' => (int) ($bookingRows[$beauticianId]->booking_total ?? 0),
          'revenue_total' => (float) ($revenueRows[$beauticianId]->revenue_total ?? 0),
        ];
      })
      ->sortByDesc(fn (array $row) => $row['revenue_total'] > 0 ? $row['revenue_total'] : $row['booking_total'])
      ->take($limit)
      ->values();

    $useRevenueMetric = $rows->sum('revenue_total') > 0;
    $labels = [];
    $amounts = [];
    $formatted = [];
    $bookingCounts = [];

    foreach ($rows as $row) {
      $labels[] = $beauticians[$row['beautician_id']] ?? ('#' . $row['beautician_id']);
      $bookingCounts[] = $row['booking_total'];

      if ($useRevenueMetric) {
        $amounts[] = $row['revenue_total'];
        $formatted[] = Money::inDefaultCurrency($row['revenue_total'])->format();
      } else {
        $amounts[] = (float) $row['booking_total'];
        $formatted[] = (string) $row['booking_total'];
      }
    }

    return [
      'title' => $useRevenueMetric
        ? trans('treatmentreservation::admin.analytics.revenue_by_beautician')
        : trans('treatmentreservation::admin.analytics.bookings_by_beautician'),
      'metric' => $useRevenueMetric ? 'revenue' : 'bookings',
      'labels' => $labels,
      'amounts' => $amounts,
      'formatted' => $formatted,
      'bookingCounts' => $bookingCounts,
      'currency' => setting('default_currency'),
    ];
  }


  /**
   * @return array{
   *     overview: array<string, mixed>,
   *     revenueTrend: array<string, mixed>,
   *     statusBreakdown: array<string, mixed>,
   *     revenueByBeautician: array<string, mixed>
   * }
   */
  public function chartPayload(int $days = self::DEFAULT_DAYS): array
  {
    return [
      'overview' => $this->overview($days),
      'revenueTrend' => $this->revenueTrend($days),
      'statusBreakdown' => $this->statusBreakdown($days),
      'revenueByBeautician' => $this->revenueByBeautician($days),
    ];
  }


  private function periodBase(string $from, string $to)
  {
    return TreatmentBooking::query()
      ->withTreatmentProduct()
      ->whereDate('appointment_date', '>=', $from)
      ->whereDate('appointment_date', '<=', $to);
  }
}
