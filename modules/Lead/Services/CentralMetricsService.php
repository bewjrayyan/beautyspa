<?php

declare(strict_types=1);

namespace Modules\Lead\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Beautician\Entities\Beautician;
use Modules\Lead\Entities\Lead;
use Modules\Order\Entities\Order;
use Modules\SpaBranch\Entities\SpaBranch;

final class CentralMetricsService
{
    public function __construct(
        private readonly CentralCheckinService $checkins,
        private readonly CentralClearanceService $clearances,
        private readonly CentralPaymentService $payments,
    ) {
    }

    public const TARGET_LEADS = 1000;

    public const TARGET_BEAUTICIAN_LEADS = 112;

    public const TARGET_CONV_PCT = 40.0;

    public const TARGET_SALES = 1_000_000.0;

    public const TARGET_AVG_SALE = 2500.0;

    /** Interim buyer target = 1000 leads × 40%. */
    public const TARGET_BUYERS = 400;

    /**
     * @return array<string, mixed>
     */
    public function build(Carbon $from, Carbon $to, ?int $branchId = null): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();
        $cacheKey = sprintf(
            'lead.central.metrics.%s.%s.%s',
            $from->toDateString(),
            $to->toDateString(),
            $branchId === null ? 'all' : (string) $branchId
        );

        return Cache::remember($cacheKey, 45, function () use ($from, $to, $branchId) {
            return $this->buildUncached($from, $to, $branchId);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function buildUncached(Carbon $from, Carbon $to, ?int $branchId = null): array
    {
        $days = max(1, $from->diffInDays($to) + 1);
        $prevTo = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

        $kpis = $this->kpis($from, $to, $branchId);
        $prev = $this->kpis($prevFrom, $prevTo, $branchId);
        $kpis['vs_prev'] = $this->deltas($kpis, $prev);

        $targets = [
            'leads' => self::TARGET_LEADS,
            'beautician_leads' => self::TARGET_BEAUTICIAN_LEADS,
            'conv_pct' => self::TARGET_CONV_PCT,
            'sales' => self::TARGET_SALES,
            'avg_sale' => self::TARGET_AVG_SALE,
            'buyers' => self::TARGET_BUYERS,
        ];

        // 2dp so early-month progress (e.g. RM440 / RM1M) is not rounded to 0.0%.
        $salesPct = self::TARGET_SALES > 0
            ? round(($kpis['sales'] / self::TARGET_SALES) * 100, 2)
            : 0.0;
        $leadsPct = self::TARGET_LEADS > 0
            ? round(($kpis['new_buyers'] / self::TARGET_LEADS) * 100, 2)
            : 0.0;
        $buyersPct = self::TARGET_BUYERS > 0
            ? round(($kpis['buyers'] / self::TARGET_BUYERS) * 100, 2)
            : 0.0;

        $beauticians = $this->beauticianRows($from, $to, $branchId);
        $branches = $this->branchRows($from, $to, $branchId);

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'label' => $from->format('M Y'),
                'key' => $from->format('Y-m'),
            ],
            'branch_id' => $branchId,
            'targets' => $targets,
            'kpis' => $kpis,
            'dual' => [
                'sales_pct' => $salesPct,
                'buyers_pct' => $buyersPct,
                'leads_pct' => $leadsPct,
            ],
            'equity' => $this->equityCurve($from, $to, $branchId),
            'heatmap' => $this->uniqueLeadHeatmap(28, $branchId),
            'leads_trend' => $this->uniqueLeadTrend($from, $to, $branchId),
            'status_mix' => $this->statusMix($from, $to, $branchId),
            // Sales waterfall stays order-based; lead funnel is in workspace.
            'waterfall' => $this->salesPipelineWaterfall($from, $to, $branchId),
            'beauticians' => $beauticians,
            'beautician_count' => Beautician::query()->where('is_active', true)->count(),
            'branches' => $branches,
            'ops' => [
                'checkin' => $this->checkins->summary([
                    'branch' => $branchId,
                    'status' => 'all',
                    'scope' => 'day',
                    'date' => now()->toDateString(),
                ]),
                'clearance' => $this->clearances->summary([
                    'branch' => $branchId,
                    'state' => 'all_queue',
                ]),
                'payments' => $this->payments->summary([
                    'branch' => $branchId,
                    'from' => $from,
                    'to' => $to,
                ]),
            ],
            'ticker' => $this->ticker($kpis, $salesPct),
            'sparks' => $this->sparks($from, $to, $branchId),
            'target_board' => [
                'sales_pct' => $salesPct,
                'sales_actual' => (float) $kpis['sales'],
                'sales_target' => self::TARGET_SALES,
                'sales_delta' => round((float) $kpis['sales'] - self::TARGET_SALES, 2),
                'above_target' => (float) $kpis['sales'] >= self::TARGET_SALES,
            ],
            'sales_insights' => $this->salesInsights($kpis, $salesPct, $beauticians, $branches, $from, $to),
        ];
    }

    /**
     * @return list<array{id:int|null,code:string,name:string}>
     */
    public function branchOptions(): array
    {
        return SpaBranch::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(fn (SpaBranch $b) => [
                'id' => (int) $b->id,
                'code' => (string) $b->code,
                'name' => (string) $b->name,
            ])
            ->values()
            ->all();
    }

    /**
     * Resolve period key to [from, to].
     *
     * Preferred: empty/"current" → current month; "Y-m" → that calendar month.
     * Legacy month abbreviations (sep/aug/jul/…) map to that calendar month in the
     * current year (not relative offsets), so e.g. sep is always September, not
     * silently aliasing to "current" when the real month is not September.
     *
     * @return array{0:Carbon,1:Carbon}
     */
    public function resolvePeriod(?string $periodKey): array
    {
        $now = Carbon::now();
        $key = strtolower(trim((string) $periodKey));

        if ($key === '' || $key === 'current') {
            return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
        }

        if (preg_match('/^(\d{4})-(\d{2})$/', $key, $m)) {
            $month = (int) $m[2];
            if ($month >= 1 && $month <= 12) {
                $start = Carbon::createFromDate((int) $m[1], $month, 1)->startOfMonth();

                return [$start, $start->copy()->endOfMonth()];
            }
        }

        // Legacy short month keys → calendar month of the current year.
        $abbr = [
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4,
            'may' => 5, 'jun' => 6, 'jul' => 7, 'aug' => 8,
            'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
        ];
        if (isset($abbr[$key])) {
            $start = Carbon::createFromDate((int) $now->year, $abbr[$key], 1)->startOfMonth();

            return [$start, $start->copy()->endOfMonth()];
        }

        return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
    }

    /**
     * @return array<string, float|int>
     */
    private function kpis(Carbon $from, Carbon $to, ?int $branchId): array
    {
        $base = $this->paidQuery($from, $to, $branchId);

        $sales = (float) (clone $base)->sum('total');
        $orders = (int) (clone $base)->count();
        $buyers = (int) (clone $base)
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->selectRaw('COUNT(DISTINCT customer_phone) as c')
            ->value('c');

        $uniqueLeads = $this->uniqueLeadCount($from, $to, $branchId);
        $convertedLeads = $this->convertedLeadCount($from, $to, $branchId);
        $conv = $uniqueLeads > 0 ? round(($convertedLeads / $uniqueLeads) * 100, 1) : 0.0;
        $avg = $buyers > 0 ? round($sales / $buyers, 2) : 0.0;

        return [
            // Alias kept for frontend: new_buyers now means unique leads (Fasa 2).
            'new_buyers' => $uniqueLeads,
            'unique_leads' => $uniqueLeads,
            'converted_leads' => $convertedLeads,
            'buyers' => $buyers,
            'new_buyer_share_pct' => $conv,
            'sales' => round($sales, 2),
            'avg_sale' => $avg,
            'orders' => $orders,
        ];
    }

    private function uniqueLeadCount(Carbon $from, Carbon $to, ?int $branchId): int
    {
        return (int) Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('is_duplicate', false)
            ->when($branchId !== null, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->count();
    }

    private function convertedLeadCount(Carbon $from, Carbon $to, ?int $branchId): int
    {
        return (int) Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('is_duplicate', false)
            ->where('status', Lead::STATUS_CONVERTED)
            ->when($branchId !== null, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->count();
    }

    /**
     * @param  array<string, float|int>  $cur
     * @param  array<string, float|int>  $prev
     * @return array<string, float>
     */
    private function deltas(array $cur, array $prev): array
    {
        $out = [];
        foreach (['new_buyers', 'buyers', 'sales', 'avg_sale'] as $key) {
            $a = (float) ($cur[$key] ?? 0);
            $b = (float) ($prev[$key] ?? 0);
            if ($b == 0.0) {
                $out[$key] = $a > 0 ? 100.0 : 0.0;
            } else {
                $out[$key] = round((($a - $b) / abs($b)) * 100, 1);
            }
        }
        // Share uses percentage-point delta (not relative %).
        $out['new_buyer_share_pct'] = round(
            (float) ($cur['new_buyer_share_pct'] ?? 0) - (float) ($prev['new_buyer_share_pct'] ?? 0),
            1
        );

        return $out;
    }

    private function newBuyerCount(Carbon $from, Carbon $to, ?int $branchId): int
    {
        $firstPaid = Order::query()
            ->paid()
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->selectRaw('customer_phone, MIN(created_at) as first_paid_at')
            ->groupBy('customer_phone');

        $q = DB::query()
            ->fromSub($firstPaid, 'fp')
            ->whereBetween('fp.first_paid_at', [$from, $to]);

        if ($branchId !== null) {
            $q->whereExists(function ($sub) use ($branchId) {
                $sub->selectRaw('1')
                    ->from('orders')
                    ->whereColumn('orders.customer_phone', 'fp.customer_phone')
                    ->whereColumn('orders.created_at', 'fp.first_paid_at')
                    ->where('orders.payment_status', Order::PAYMENT_PAID)
                    ->where('orders.spa_branch_id', $branchId)
                    ->whereNull('orders.deleted_at');
            });
        }

        return (int) $q->count();
    }

    /**
     * @return array{labels:list<string>,actual:list<float>,target_path:list<float>}
     */
    private function equityCurve(Carbon $from, Carbon $to, ?int $branchId): array
    {
        $end = Carbon::now()->lt($to) ? Carbon::now()->endOfDay() : $to->copy();
        if ($end->lt($from)) {
            $end = $from->copy();
        }

        $rows = $this->paidQuery($from, $end, $branchId)
            ->selectRaw('DATE(created_at) as d')
            ->selectRaw('SUM(total) as amount')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('amount', 'd');

        $labels = [];
        $actual = [];
        $targetPath = [];
        $running = 0.0;
        $monthDays = max(1, $from->daysInMonth);
        $dayIndex = 0;

        for ($cursor = $from->copy()->startOfDay(); $cursor->lte($end); $cursor->addDay()) {
            $key = $cursor->toDateString();
            $running += (float) ($rows[$key] ?? 0);
            $labels[] = $cursor->format('d M');
            $actual[] = round($running, 2);
            $dayIndex++;
            $targetPath[] = round((self::TARGET_SALES / $monthDays) * $dayIndex, 2);
        }

        if ($labels === []) {
            $labels = [$from->format('d M')];
            $actual = [0.0];
            $targetPath = [0.0];
        }

        return [
            'labels' => $labels,
            'actual' => $actual,
            'target_path' => $targetPath,
        ];
    }

    /**
     * @return list<array{d:string,v:int}>
     */
    private function uniqueLeadHeatmap(int $days, ?int $branchId): array
    {
        $end = Carbon::now()->endOfDay();
        $start = Carbon::now()->subDays($days - 1)->startOfDay();

        $map = Lead::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('is_duplicate', false)
            ->when($branchId !== null, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->selectRaw('DATE(created_at) as d')
            ->selectRaw('COUNT(*) as c')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('c', 'd');

        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $start->copy()->addDays($i);
            $out[] = [
                'd' => $d->format('m-d'),
                'v' => (int) ($map[$d->toDateString()] ?? 0),
            ];
        }

        return $out;
    }

    /**
     * Daily unique (non-duplicate) leads within the selected period.
     *
     * @return array{labels: list<string>, actual: list<int>}
     */
    private function uniqueLeadTrend(Carbon $from, Carbon $to, ?int $branchId): array
    {
        $map = Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('is_duplicate', false)
            ->when($branchId !== null, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->selectRaw('DATE(created_at) as d')
            ->selectRaw('COUNT(*) as c')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('c', 'd');

        $labels = [];
        $actual = [];
        $cursor = $from->copy()->startOfDay();
        $last = $to->copy()->startOfDay();
        while ($cursor->lte($last)) {
            $labels[] = $cursor->format('d M');
            $actual[] = (int) ($map[$cursor->toDateString()] ?? 0);
            $cursor->addDay();
        }

        return compact('labels', 'actual');
    }

    /**
     * @return list<array{d:string,v:int}>
     * @deprecated Kept for compatibility; prefer uniqueLeadHeatmap.
     */
    private function newBuyerHeatmap(int $days, ?int $branchId): array
    {
        return $this->uniqueLeadHeatmap($days, $branchId);
    }

    /**
     * @return list<array{name: string, value: int}>
     */
    private function salesPipelineWaterfall(Carbon $from, Carbon $to, ?int $branchId): array
    {
        $booked = (int) Order::query()
            ->withoutCanceledOrders()
            ->whereBetween('created_at', [$from, $to])
            ->when($branchId !== null, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->count();

        $paid = (int) $this->paidQuery($from, $to, $branchId)->count();
        $completed = (int) $this->paidQuery($from, $to, $branchId)
            ->where('status', Order::COMPLETED)
            ->count();

        return [
            ['name' => trans('lead::central.overview.funnel_book'), 'value' => $booked],
            ['name' => trans('lead::central.overview.funnel_pay'), 'value' => $paid],
            ['name' => trans('lead::central.overview.funnel_treat_done'), 'value' => $completed],
        ];
    }

    /**
     * @return list<array{name: string, value: int, key: string}>
     */
    private function statusMix(Carbon $from, Carbon $to, ?int $branchId): array
    {
        $rows = Order::query()
            ->withoutCanceledOrders()
            ->whereBetween('created_at', [$from, $to])
            ->when($branchId !== null, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $order = ['pending', 'processing', 'completed'];
        $out = [];
        foreach ($order as $status) {
            if (isset($rows[$status])) {
                $label = trans("order::statuses.{$status}");
                $out[] = [
                    'name' => $label === "order::statuses.{$status}" ? $status : $label,
                    'value' => (int) $rows[$status],
                    'key' => $status,
                ];
            }
        }
        foreach ($rows as $status => $count) {
            if (! in_array($status, $order, true) && $status !== 'canceled') {
                $label = trans("order::statuses.{$status}");
                $out[] = [
                    'name' => $label === "order::statuses.{$status}" ? (string) $status : $label,
                    'value' => (int) $count,
                    'key' => (string) $status,
                ];
            }
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function beauticianRows(Carbon $from, Carbon $to, ?int $branchId): array
    {
        $salesRows = Order::query()
            ->paid()
            ->whereBetween('orders.created_at', [$from, $to])
            ->when($branchId !== null, fn ($q) => $q->where('orders.spa_branch_id', $branchId))
            ->whereNotNull('orders.beautician_id')
            ->selectRaw('orders.beautician_id as id')
            ->selectRaw('SUM(orders.total) as sales')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw("COUNT(DISTINCT NULLIF(orders.customer_phone, '')) as buyers")
            ->groupBy('orders.beautician_id')
            ->orderByDesc('sales')
            ->get()
            ->keyBy('id');

        $leadsByBeautician = $this->uniqueLeadsByBeautician($from, $to, $branchId);
        $convertedByBeautician = $this->convertedLeadsByBeautician($from, $to, $branchId);
        $attributedIds = collect(array_keys($leadsByBeautician))
            ->merge(array_keys($convertedByBeautician))
            ->merge($salesRows->keys())
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        // Keep active staff visible, while retaining historical attribution for
        // inactive staff who still have records in the selected period.
        $beauticians = Beautician::query()
            ->without(['files', 'user'])
            ->where(function ($query) use ($attributedIds): void {
                $query->where('is_active', true);
                if ($attributedIds->isNotEmpty()) {
                    $query->orWhereIn('id', $attributedIds->all());
                }
            })
            ->orderBy('position')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name'])
            ->keyBy('id');

        $out = [];
        foreach ($beauticians as $id => $beautician) {
            $salesRow = $salesRows->get($id);
            $buyers = (int) ($salesRow->buyers ?? 0);
            $sales = (float) ($salesRow->sales ?? 0);
            $leads = (int) ($leadsByBeautician[(int) $id] ?? 0);
            $converted = (int) ($convertedByBeautician[(int) $id] ?? 0);
            $conv = $leads > 0 ? round(($converted / $leads) * 100, 1) : 0.0;
            $out[] = [
                'id' => (int) $id,
                'name' => (string) $beautician->name,
                'leads' => $leads,
                'target' => self::TARGET_BEAUTICIAN_LEADS,
                'buyers' => $buyers,
                'converted' => $converted,
                'conv' => $conv,
                'sales' => round($sales, 2),
                'orders' => (int) ($salesRow->order_count ?? 0),
                'avg' => $buyers > 0 ? round($sales / $buyers, 2) : 0.0,
                'follow' => 0,
                'lost' => 0,
                'performance' => $this->performanceLabel($conv, $sales),
            ];
        }

        $unassignedLeads = $this->unassignedLeadCount($from, $to, $branchId);
        $unassignedConverted = $this->unassignedConvertedLeadCount($from, $to, $branchId);
        $unassignedSales = $this->unassignedSalesRow($from, $to, $branchId);

        if ($unassignedLeads > 0 || $unassignedConverted > 0 || $unassignedSales !== null) {
            $buyers = (int) ($unassignedSales?->buyers ?? 0);
            $sales = (float) ($unassignedSales?->sales ?? 0);
            $conv = $unassignedLeads > 0
                ? round(($unassignedConverted / $unassignedLeads) * 100, 1)
                : 0.0;
            $out[] = [
                'id' => null,
                'name' => trans('lead::central.reporting.unassigned'),
                'leads' => $unassignedLeads,
                'target' => self::TARGET_BEAUTICIAN_LEADS,
                'buyers' => $buyers,
                'converted' => $unassignedConverted,
                'conv' => $conv,
                'sales' => round($sales, 2),
                'orders' => (int) ($unassignedSales?->order_count ?? 0),
                'avg' => $buyers > 0 ? round($sales / $buyers, 2) : 0.0,
                'follow' => 0,
                'lost' => 0,
                'performance' => $this->performanceLabel($conv, $sales),
            ];
        }

        usort($out, static function (array $left, array $right): int {
            return [$right['leads'], $right['converted'], $right['sales'], $left['name']]
                <=> [$left['leads'], $left['converted'], $left['sales'], $right['name']];
        });

        return $out;
    }

    private function unassignedLeadCount(Carbon $from, Carbon $to, ?int $branchId): int
    {
        return (int) Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('is_duplicate', false)
            ->whereNull('beautician_id')
            ->when($branchId !== null, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->count();
    }

    private function unassignedConvertedLeadCount(Carbon $from, Carbon $to, ?int $branchId): int
    {
        return (int) Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('is_duplicate', false)
            ->where('status', Lead::STATUS_CONVERTED)
            ->whereNull('beautician_id')
            ->when($branchId !== null, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->count();
    }

    private function unassignedSalesRow(Carbon $from, Carbon $to, ?int $branchId): ?object
    {
        $row = Order::query()
            ->paid()
            ->whereBetween('orders.created_at', [$from, $to])
            ->when($branchId !== null, fn ($q) => $q->where('orders.spa_branch_id', $branchId))
            ->whereNull('orders.beautician_id')
            ->selectRaw('SUM(orders.total) as sales')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw("COUNT(DISTINCT NULLIF(orders.customer_phone, '')) as buyers")
            ->first();

        return (int) ($row?->order_count ?? 0) > 0 ? $row : null;
    }

    /**
     * @return array<int, int>
     */
    private function uniqueLeadsByBeautician(Carbon $from, Carbon $to, ?int $branchId): array
    {
        return Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('is_duplicate', false)
            ->whereNotNull('beautician_id')
            ->when($branchId !== null, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->selectRaw('beautician_id as id')
            ->selectRaw('COUNT(*) as c')
            ->groupBy('beautician_id')
            ->pluck('c', 'id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function convertedLeadsByBeautician(Carbon $from, Carbon $to, ?int $branchId): array
    {
        return Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('is_duplicate', false)
            ->where('status', Lead::STATUS_CONVERTED)
            ->whereNotNull('beautician_id')
            ->when($branchId !== null, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->selectRaw('beautician_id as id')
            ->selectRaw('COUNT(*) as c')
            ->groupBy('beautician_id')
            ->pluck('c', 'id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @return array<int, int>
     * @deprecated Prefer uniqueLeadsByBeautician.
     */
    private function newBuyersByBeautician(Carbon $from, Carbon $to, ?int $branchId): array
    {
        return $this->uniqueLeadsByBeautician($from, $to, $branchId);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function branchRows(Carbon $from, Carbon $to, ?int $branchId): array
    {
        $snapshot = Order::salesSnapshot($from, $to);
        $buyerMap = $this->paidQuery($from, $to, null)
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->selectRaw('spa_branch_id')
            ->selectRaw('COUNT(DISTINCT customer_phone) as buyers')
            ->groupBy('spa_branch_id')
            ->pluck('buyers', 'spa_branch_id');

        $newMap = $this->newBuyersByBranch($from, $to);

        $out = [];
        foreach ($snapshot['by_branch'] as $row) {
            $id = $row['branch_id'];
            if ($branchId !== null && $id !== $branchId) {
                continue;
            }
            $buyers = (int) ($buyerMap[$id] ?? 0);
            $newBuyers = (int) ($newMap[$id === null ? 'none' : (string) $id] ?? 0);
            $sales = (float) ($row['net'] ?? 0);
            $conv = $newBuyers > 0
                ? round((($this->convertedLeadCountByBranch($from, $to, $id) / $newBuyers) * 100), 1)
                : 0.0;
            $out[] = [
                'id' => $id,
                'code' => '',
                'name' => (string) $row['name'],
                'sales' => round($sales, 2),
                'booked' => round((float) ($row['total'] ?? 0), 2),
                'buyers' => $buyers,
                'new_buyers' => $newBuyers,
                'avg' => $buyers > 0 ? round($sales / $buyers, 2) : 0.0,
                'avg_sale' => $buyers > 0 ? round($sales / $buyers, 2) : 0.0,
                'conv' => $conv,
                'new_buyer_share_pct' => $conv,
                'completed' => 0,
            ];
        }

        // Prefer active spa_branches list (even with zero sales).
        $active = SpaBranch::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        if ($active->isNotEmpty() && $branchId === null) {
            $byId = collect($out)->keyBy(fn ($r) => $r['id'] === null ? 'none' : (string) $r['id']);
            $merged = [];
            foreach ($active as $branch) {
                $key = (string) $branch->id;
                $existing = $byId->get($key);
                if ($existing) {
                    $existing['code'] = (string) $branch->code;
                    $existing['name'] = (string) $branch->name;
                    $merged[] = $existing;
                } else {
                    $merged[] = [
                        'id' => (int) $branch->id,
                        'code' => (string) $branch->code,
                        'name' => (string) $branch->name,
                        'sales' => 0.0,
                        'booked' => 0.0,
                        'buyers' => 0,
                        'new_buyers' => 0,
                        'avg' => 0.0,
                        'avg_sale' => 0.0,
                        'conv' => 0.0,
                        'new_buyer_share_pct' => 0.0,
                        'completed' => 0,
                    ];
                }
            }
            usort($merged, static fn ($a, $b) => $b['sales'] <=> $a['sales']);

            return $merged;
        }

        return $out;
    }

    /**
     * @return array<string, int>
     */
    private function newBuyersByBranch(Carbon $from, Carbon $to): array
    {
        $rows = Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('is_duplicate', false)
            ->selectRaw('spa_branch_id as branch_id')
            ->selectRaw('COUNT(*) as c')
            ->groupBy('spa_branch_id')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $key = $row->branch_id === null ? 'none' : (string) $row->branch_id;
            $map[$key] = (int) $row->c;
        }

        return $map;
    }

    private function convertedLeadCountByBranch(Carbon $from, Carbon $to, ?int $branchId): int
    {
        return (int) Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('is_duplicate', false)
            ->where('status', Lead::STATUS_CONVERTED)
            ->when(
                $branchId === null,
                fn ($q) => $q->whereNull('spa_branch_id'),
                fn ($q) => $q->where('spa_branch_id', $branchId)
            )
            ->count();
    }

    /**
     * @param  array<string, float|int>  $kpis
     * @return array<string, string>
     */
    private function ticker(array $kpis, float $salesPct): array
    {
        $vs = $kpis['vs_prev'] ?? [];

        return [
            'leads' => number_format((int) $kpis['new_buyers']),
            'leadsDelta' => $this->deltaLabel($vs['new_buyers'] ?? 0),
            'conv' => number_format((float) $kpis['new_buyer_share_pct'], 1) . '%',
            'convDelta' => $this->deltaLabel($vs['new_buyer_share_pct'] ?? 0, 'pp'),
            'sales' => $this->moneyShort((float) $kpis['sales']),
            'salesDelta' => $this->deltaLabel($vs['sales'] ?? 0),
            'targetPct' => number_format($salesPct, 1) . '%',
            'targetDelta' => $this->deltaLabel($salesPct - 100, 'pp'),
            'avg' => 'RM' . number_format((float) $kpis['avg_sale']),
            'avgDelta' => $this->deltaLabel($vs['avg_sale'] ?? 0),
        ];
    }

    private function deltaLabel(float $pct, string $suffix = '%'): string
    {
        $sign = $pct >= 0 ? '' : '';
        $abs = abs($pct);

        return ($pct >= 0 ? '' : '-') . number_format($abs, 1) . $suffix;
    }

    private function moneyShort(float $amount): string
    {
        if ($amount >= 1_000_000) {
            return 'RM' . rtrim(rtrim(number_format($amount / 1_000_000, 3, '.', ''), '0'), '.') . 'M';
        }
        if ($amount >= 1_000) {
            return 'RM' . rtrim(rtrim(number_format($amount / 1_000, 1, '.', ''), '0'), '.') . 'k';
        }

        return 'RM' . number_format($amount, 0);
    }

    /**
     * @return list<array{id:string,values:list<float>,up:bool}>
     */
    private function sparks(Carbon $from, Carbon $to, ?int $branchId): array
    {
        $end = Carbon::now()->lt($to) ? Carbon::now()->endOfDay() : $to->copy();
        $start = $end->copy()->subDays(6)->startOfDay();
        if ($start->lt($from)) {
            $start = $from->copy()->startOfDay();
        }

        $dailySales = $this->paidQuery($start, $end, $branchId)
            ->selectRaw('DATE(created_at) as d')
            ->selectRaw('SUM(total) as amount')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('amount', 'd');

        $dailyBuyers = $this->paidQuery($start, $end, $branchId)
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->selectRaw('DATE(created_at) as d')
            ->selectRaw('COUNT(DISTINCT customer_phone) as c')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('c', 'd');

        $dailyNew = $this->uniqueLeadCountsByDate($start, $end, $branchId);

        $sales = [];
        $buyers = [];
        $new = [];
        $avg = [];
        $share = [];

        for ($cursor = $start->copy(); $cursor->lte($end); $cursor->addDay()) {
            $key = $cursor->toDateString();
            $s = (float) ($dailySales[$key] ?? 0);
            $b = (int) ($dailyBuyers[$key] ?? 0);
            $n = (int) ($dailyNew[$key] ?? 0);
            $sales[] = round($s / 1000, 2);
            $buyers[] = (float) $b;
            $new[] = (float) $n;
            $avg[] = $b > 0 ? round($s / $b, 0) : 0.0;
            // Conversion spark uses lead conversion proxy: unique leads that day vs buyers (0 if no leads).
            $share[] = $n > 0 ? round(min(100, ($b / max($n, 1)) * 100), 1) : 0.0;
        }

        $up = static fn (array $vals): bool => count($vals) < 2 || $vals[count($vals) - 1] >= $vals[0];

        return [
            ['id' => 'sparkLeads', 'values' => $new, 'up' => $up($new)],
            ['id' => 'sparkBuyers', 'values' => $buyers, 'up' => $up($buyers)],
            ['id' => 'sparkConv', 'values' => $share, 'up' => $up($share)],
            ['id' => 'sparkSales', 'values' => $sales, 'up' => $up($sales)],
            ['id' => 'sparkAvg', 'values' => $avg, 'up' => $up($avg)],
        ];
    }

    /**
     * Unique lead counts keyed by created date (Y-m-d).
     *
     * @return array<string, int>
     */
    private function uniqueLeadCountsByDate(Carbon $from, Carbon $to, ?int $branchId): array
    {
        return Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('is_duplicate', false)
            ->when($branchId !== null, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->selectRaw('DATE(created_at) as d')
            ->selectRaw('COUNT(*) as c')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('c', 'd')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @return array<string, int>
     * @deprecated Prefer uniqueLeadCountsByDate.
     */
    private function newBuyerCountsByDate(Carbon $from, Carbon $to, ?int $branchId): array
    {
        return $this->uniqueLeadCountsByDate($from, $to, $branchId);
    }

    private function paidQuery(Carbon $from, Carbon $to, ?int $branchId)
    {
        $q = Order::query()
            ->paid()
            ->whereBetween('created_at', [$from, $to]);

        if ($branchId !== null) {
            $q->where('spa_branch_id', $branchId);
        }

        return $q;
    }

    /**
     * Marketing-facing sales narrative chips for the Sales Overview page.
     *
     * @param  array<string, float|int>  $kpis
     * @param  list<array<string, mixed>>  $beauticians
     * @param  list<array<string, mixed>>  $branches
     * @return list<array{tone:string,title:string,body:string}>
     */
    private function salesInsights(
        array $kpis,
        float $salesPct,
        array $beauticians,
        array $branches,
        Carbon $from,
        Carbon $to
    ): array {
        $sales = (float) ($kpis['sales'] ?? 0);
        $avg = (float) ($kpis['avg_sale'] ?? 0);
        $buyers = (int) ($kpis['buyers'] ?? 0);
        $gap = max(0.0, self::TARGET_SALES - $sales);
        $out = [];

        if ($salesPct >= 100) {
            $out[] = [
                'tone' => 'ok',
                'title' => trans('lead::central.sales.insight_target_hit_title'),
                'body' => trans('lead::central.sales.insight_target_hit_body', [
                    'pct' => number_format($salesPct, 1),
                ]),
            ];
        } else {
            $daysTotal = max(1, $from->diffInDays($to) + 1);
            $paceEnd = now()->lt($to) ? now() : $to;
            $daysElapsed = max(1, $from->diffInDays($paceEnd) + 1);
            $pace = ($sales / $daysElapsed) * $daysTotal;
            $out[] = [
                'tone' => $pace >= self::TARGET_SALES ? 'info' : 'warn',
                'title' => trans('lead::central.sales.insight_gap_title'),
                'body' => trans('lead::central.sales.insight_gap_body', [
                    'gap' => 'RM' . number_format($gap, 0),
                    'pace' => 'RM' . number_format($pace, 0),
                ]),
            ];
        }

        if ($avg > 0 && $avg < self::TARGET_AVG_SALE) {
            $out[] = [
                'tone' => 'warn',
                'title' => trans('lead::central.sales.insight_avg_title'),
                'body' => trans('lead::central.sales.insight_avg_body', [
                    'avg' => 'RM' . number_format($avg, 0),
                    'target' => 'RM' . number_format(self::TARGET_AVG_SALE, 0),
                ]),
            ];
        } elseif ($avg >= self::TARGET_AVG_SALE) {
            $out[] = [
                'tone' => 'ok',
                'title' => trans('lead::central.sales.insight_avg_ok_title'),
                'body' => trans('lead::central.sales.insight_avg_ok_body', [
                    'avg' => 'RM' . number_format($avg, 0),
                ]),
            ];
        }

        $topBeautician = collect($beauticians)
            ->filter(fn ($row) => ($row['id'] ?? null) !== null)
            ->sortByDesc(fn ($row) => (float) ($row['sales'] ?? 0))
            ->first();
        if (is_array($topBeautician) && (float) ($topBeautician['sales'] ?? 0) > 0) {
            $out[] = [
                'tone' => 'info',
                'title' => trans('lead::central.sales.insight_top_beautician_title'),
                'body' => trans('lead::central.sales.insight_top_beautician_body', [
                    'name' => (string) ($topBeautician['name'] ?? '—'),
                    'sales' => 'RM' . number_format((float) $topBeautician['sales'], 0),
                ]),
            ];
        }

        $topBranch = $branches[0] ?? null;
        if (is_array($topBranch) && (float) ($topBranch['sales'] ?? 0) > 0) {
            $out[] = [
                'tone' => 'info',
                'title' => trans('lead::central.sales.insight_top_branch_title'),
                'body' => trans('lead::central.sales.insight_top_branch_body', [
                    'name' => (string) ($topBranch['name'] ?? '—'),
                    'sales' => 'RM' . number_format((float) $topBranch['sales'], 0),
                ]),
            ];
        }

        if ($buyers > 0 && $gap > 0 && self::TARGET_AVG_SALE > 0) {
            $needCustomers = (int) ceil($gap / self::TARGET_AVG_SALE);
            $out[] = [
                'tone' => 'info',
                'title' => trans('lead::central.sales.insight_customers_title'),
                'body' => trans('lead::central.sales.insight_customers_body', [
                    'count' => $needCustomers,
                ]),
            ];
        }

        return array_slice($out, 0, 5);
    }

    private function performanceLabel(float $conv, float $sales): string
    {
        if ($conv >= 40 && $sales >= 50_000) {
            return 'Excellent';
        }
        if ($conv >= 35) {
            return 'Good';
        }

        return 'Needs Attention';
    }
}
