<?php

declare(strict_types=1);

namespace Modules\Lead\Services;

use Carbon\Carbon;
use Modules\Beautician\Entities\Beautician;
use Modules\Lead\Entities\Lead;
use Modules\Lead\Entities\LeadImport;
use Modules\Order\Entities\Order;
use Modules\SpaBranch\Entities\SpaBranch;

final class CentralReportingService
{
    public function performance(string $view, Carbon $from, Carbon $to, ?int $branchId): array
    {
        $column = $view === 'beauticians' ? 'beautician_id' : 'spa_branch_id';
        $leads = Lead::query()->where('is_duplicate', false)
            ->whereBetween('created_at', [$from, $to])
            ->when($branchId !== null, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->selectRaw("{$column} as group_id, COUNT(*) as leads")
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as converted', [Lead::STATUS_CONVERTED])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as follow_up', [Lead::STATUS_FOLLOW_UP])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as lost', [Lead::STATUS_LOST])
            ->groupBy($column)->get()->keyBy(fn ($r) => (string) ($r->group_id ?? 0));
        $sales = Order::query()->paid()->whereBetween('created_at', [$from, $to])
            ->when($branchId !== null, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->selectRaw("{$column} as group_id, COUNT(*) as orders, SUM(total) as revenue")
            ->groupBy($column)->get()->keyBy(fn ($r) => (string) ($r->group_id ?? 0));
        if ($view === 'beauticians') {
            $names = Beautician::query()->without(['files', 'user'])
                ->get(['id', 'first_name', 'last_name'])->mapWithKeys(fn ($b) => [$b->id => trim($b->first_name.' '.$b->last_name)]);
            // A branch-scoped report includes only staff with records in that branch.
            if ($branchId !== null) {
                $names = $names->only($leads->keys()->merge($sales->keys())->all());
            }
        } else {
            $names = SpaBranch::query()->when($branchId !== null, fn ($q) => $q->whereKey($branchId))->pluck('name', 'id');
        }
        foreach ($leads->keys()->merge($sales->keys())->unique() as $id) {
            if (! $names->has($id)) {
                $names->put($id, (int) $id === 0 ? trans('lead::central.reporting.unassigned') : '#'.$id);
            }
        }
        $rows = $names->map(function ($name, $id) use ($leads, $sales): array {
            $lead = $leads->get((string) $id);
            $sale = $sales->get((string) $id);
            $count = (int) ($lead?->leads ?? 0);
            $orders = (int) ($sale?->orders ?? 0);
            $revenue = round((float) ($sale?->revenue ?? 0), 2);
            $converted = (int) ($lead?->converted ?? 0);
            return [
                'id' => (int) $id, 'name' => $name, 'leads' => $count,
                'converted' => $converted, 'conversion' => $count ? round($converted / $count * 100, 1) : 0,
                'follow_up' => (int) ($lead?->follow_up ?? 0), 'lost' => (int) ($lead?->lost ?? 0),
                'orders' => $orders, 'revenue' => $revenue, 'average_order' => $orders ? round($revenue / $orders, 2) : 0,
            ];
        })->sortBy([['revenue', 'desc'], ['leads', 'desc'], ['name', 'asc']])->values();
        $leadCount = (int) $rows->sum('leads');
        return ['data' => $rows->all(), 'meta' => ['summary' => [
            'leads' => $leadCount, 'converted' => (int) $rows->sum('converted'),
            'conversion' => $leadCount ? round($rows->sum('converted') / $leadCount * 100, 1) : 0,
            'orders' => (int) $rows->sum('orders'), 'revenue' => round($rows->sum('revenue'), 2),
        ]]];
    }

    public function audit(Carbon $from, Carbon $to, ?int $branchId, string $search = '', int $page = 1): array
    {
        // Import batches are the recorded Lead audit source. Do not infer historical edits from updated_at.
        $query = LeadImport::query()->whereBetween('created_at', [$from, $to])
            ->when($branchId !== null, fn ($q) => $q->where('spa_branch_id', $branchId))
            ->when($search !== '', fn ($q) => $q->where('batch_code', 'like', '%'.$search.'%'));
        $summary = (clone $query)->selectRaw('COUNT(*) as batches, COALESCE(SUM(imported_count),0) as imported, COALESCE(SUM(duplicate_count),0) as duplicates, COALESCE(SUM(invalid_count),0) as invalid')->first();
        $pager = $query->with(['uploader:id,first_name,last_name', 'spaBranch:id,name'])
            ->latest('id')->paginate(25, ['*'], 'page', $page);
        return ['data' => collect($pager->items())->map(fn ($b) => [
            'id' => $b->id, 'code' => $b->batch_code, 'date' => $b->created_at?->format('d M Y H:i'),
            'actor' => trim(($b->uploader?->first_name ?? '').' '.($b->uploader?->last_name ?? '')) ?: '—',
            'branch' => $b->spaBranch?->name ?: trans('lead::central.reporting.unassigned'),
            'method' => strtoupper($b->method), 'status' => $b->status,
            'raw' => (int) $b->raw_count, 'imported' => (int) $b->imported_count,
            'duplicates' => (int) $b->duplicate_count, 'invalid' => (int) $b->invalid_count,
        ])->all(), 'meta' => ['summary' => [
            'batches' => (int) $summary->batches, 'imported' => (int) $summary->imported,
            'duplicates' => (int) $summary->duplicates, 'invalid' => (int) $summary->invalid,
        ], 'current_page' => $pager->currentPage(), 'last_page' => $pager->lastPage(), 'total' => $pager->total()]];
    }
}
