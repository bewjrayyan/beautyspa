<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Lead\Services\CentralCustomerService;
use Modules\Lead\Services\CentralMetricsService;
use Modules\User\Entities\User;

final class CentralCustomerController
{
    public function __construct(
        private readonly CentralCustomerService $customers,
        private readonly CentralMetricsService $metrics,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        [$from, $to] = $this->metrics->resolvePeriod($request->query('period'));

        $branchFilter = $request->query('branch', $request->query('spa_branch_id'));
        $branchId = ($branchFilter === null || $branchFilter === '' || $branchFilter === 'all')
            ? null
            : (int) $branchFilter;
        if ($branchId !== null && $branchId <= 0) {
            $branchId = null;
        }

        $filters = [
            'q' => $request->query('q'),
            'segment' => $request->query('segment', 'all'),
            'branch' => $branchFilter,
            'from' => $from,
            'to' => $to,
            'per_page' => (int) $request->query('per_page', 25),
        ];

        $paginator = $this->customers->paginate($filters);

        $data = collect($paginator->items())
            ->map(fn (User $user) => $this->customers->toArray($user))
            ->values()
            ->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'summary' => $this->customers->summary([
                    'branch' => $branchId,
                    'from' => $from,
                    'to' => $to,
                ]),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'period' => $from->format('Y-m'),
            ],
            'filters' => [
                'segments' => $this->customers->segmentOptions(),
                'branches' => $this->customers->branchOptions(),
            ],
        ]);
    }
}
