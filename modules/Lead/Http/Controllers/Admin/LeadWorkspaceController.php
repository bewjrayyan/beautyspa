<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Lead\Entities\Lead;
use Modules\Lead\Http\Requests\Admin\StoreLeadRequest;
use Modules\Lead\Http\Requests\Admin\UpdateLeadRequest;
use Modules\Lead\Http\Requests\Admin\UpdateLeadStatusRequest;
use Modules\Lead\Services\LeadWorkspaceService;

final class LeadWorkspaceController
{
    public function __construct(
        private readonly LeadWorkspaceService $workspace,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $branchFilter = $request->query('branch', $request->query('spa_branch_id'));
        $branchId = ($branchFilter === null || $branchFilter === '' || $branchFilter === 'all')
            ? null
            : (int) $branchFilter;

        if ($branchId !== null && $branchId <= 0) {
            $branchId = null;
        }

        $month = $request->query('month');
        $monthKey = is_string($month) ? $month : null;

        $paginator = $this->workspace->paginate([
            'q' => $request->query('q'),
            'status' => $request->query('status'),
            'branch' => $branchFilter,
            'beautician' => $request->query('beautician', $request->query('beautician_id')),
            'month' => $monthKey,
            'per_page' => (int) $request->query('per_page', 25),
        ]);

        $data = collect($paginator->items())
            ->map(fn (Lead $lead) => $this->workspace->toArray($lead))
            ->values()
            ->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'summary' => $this->workspace->summary($branchId, $monthKey),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'month' => $monthKey,
            ],
            'filters' => [
                'statuses' => $this->workspace->statusOptions(),
                'beauticians' => $this->workspace->beauticianOptions(),
                'branches' => $this->workspace->branchOptions(),
                'months' => $this->workspace->monthOptions(12),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $lead = Lead::query()
            ->with(['spaBranch', 'beautician', 'customer'])
            ->findOrFail($id);

        return response()->json([
            'data' => $this->workspace->toArray($lead),
            'filters' => [
                'statuses' => $this->workspace->statusOptions(),
                'beauticians' => $this->workspace->beauticianOptions(),
                'branches' => $this->workspace->branchOptions(),
            ],
        ]);
    }

    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = $this->workspace->create($request->payload());

        return response()->json([
            'message' => trans('lead::central.workspace.saved'),
            'data' => $this->workspace->toArray($lead),
        ], 201);
    }

    public function update(UpdateLeadRequest $request, int $id): JsonResponse
    {
        $lead = Lead::query()->findOrFail($id);
        $lead = $this->workspace->update($lead, $request->payload());

        return response()->json([
            'message' => trans('lead::central.workspace.updated'),
            'data' => $this->workspace->toArray($lead),
        ]);
    }

    public function updateStatus(UpdateLeadStatusRequest $request, int $id): JsonResponse
    {
        $lead = Lead::query()->findOrFail($id);
        $lead = $this->workspace->updateStatus($lead, (string) $request->validated('status'));

        return response()->json([
            'message' => trans('lead::central.workspace.status_updated'),
            'data' => $this->workspace->toArray($lead),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $lead = Lead::query()->findOrFail($id);
        $this->workspace->delete($lead);

        return response()->json([
            'message' => trans('lead::central.workspace.deleted'),
        ]);
    }

    public function followUps(Request $request): JsonResponse
    {
        $branchFilter = $request->query('branch', $request->query('spa_branch_id'));
        $branchId = ($branchFilter === null || $branchFilter === '' || $branchFilter === 'all')
            ? null
            : (int) $branchFilter;

        if ($branchId !== null && $branchId <= 0) {
            $branchId = null;
        }

        $paginator = $this->workspace->paginateFollowUps([
            'q' => $request->query('q'),
            'bucket' => $request->query('bucket', 'all'),
            'branch' => $branchFilter,
            'beautician' => $request->query('beautician', $request->query('beautician_id')),
            'per_page' => (int) $request->query('per_page', 25),
        ]);

        $data = collect($paginator->items())
            ->map(fn (Lead $lead) => $this->workspace->toArray($lead))
            ->values()
            ->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'summary' => $this->workspace->followUpSummary($branchId),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'bucket' => (string) $request->query('bucket', 'all'),
            ],
            'filters' => [
                'statuses' => $this->workspace->statusOptions(),
                'beauticians' => $this->workspace->beauticianOptions(),
                'branches' => $this->workspace->branchOptions(),
                'buckets' => [
                    ['value' => 'all', 'label' => trans('lead::central.followup.bucket_all')],
                    ['value' => 'overdue', 'label' => trans('lead::central.followup.bucket_overdue')],
                    ['value' => 'due_today', 'label' => trans('lead::central.followup.bucket_due_today')],
                    ['value' => 'no_response', 'label' => trans('lead::central.followup.bucket_no_response')],
                    ['value' => 'lost', 'label' => trans('lead::central.followup.bucket_lost')],
                ],
            ],
        ]);
    }

    public function markFollowedUp(int $id): JsonResponse
    {
        $lead = Lead::query()->findOrFail($id);
        $lead = $this->workspace->markFollowedUp($lead);

        return response()->json([
            'message' => trans('lead::central.followup.marked'),
            'data' => $this->workspace->toArray($lead),
        ]);
    }
}
