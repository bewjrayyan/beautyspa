<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Lead\Services\CentralMetricsService;
use Modules\Lead\Services\CentralPaymentService;
use Modules\Order\Entities\Order;

final class CentralPaymentController
{
    public function __construct(
        private readonly CentralPaymentService $payments,
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

        $customerId = $request->query('customer_id');
        if ($customerId === null || $customerId === '' || $customerId === 'all') {
            $customerId = null;
        } else {
            $customerId = (int) $customerId;
            if ($customerId <= 0) {
                $customerId = null;
            }
        }

        $filters = [
            'q' => $request->query('q'),
            'customer_id' => $customerId,
            'status' => $request->query('status', 'all'),
            'branch' => $branchFilter,
            'beautician' => $request->query('beautician', $request->query('beautician_id')),
            'from' => $from,
            'to' => $to,
            'per_page' => (int) $request->query('per_page', 25),
        ];

        $paginator = $this->payments->paginate($filters);

        $canViewProof = $request->user()?->hasAccess('admin.orders.show') ?? false;
        $data = collect($paginator->items())
            ->map(fn (Order $order) => $this->payments->toArray($order, $canViewProof))
            ->values()
            ->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'summary' => $this->payments->summary([
                    'customer_id' => $customerId,
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
                'statuses' => $this->payments->statusOptions(),
                'beauticians' => $this->payments->beauticianOptions(),
                'branches' => $this->payments->branchOptions(),
            ],
        ]);
    }
}
