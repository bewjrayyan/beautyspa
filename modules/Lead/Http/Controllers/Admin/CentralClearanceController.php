<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Lead\Services\CentralClearanceService;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

final class CentralClearanceController
{
    public function __construct(
        private readonly CentralClearanceService $clearances,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $branchFilter = $request->query('branch', $request->query('spa_branch_id'));

        $filters = [
            'q' => $request->query('q'),
            'state' => $request->query('state', 'waiting'),
            'branch' => $branchFilter,
            'beautician' => $request->query('beautician', $request->query('beautician_id')),
            'per_page' => (int) $request->query('per_page', 25),
        ];

        $paginator = $this->clearances->paginate($filters);

        $data = collect($paginator->items())
            ->map(fn (TreatmentBooking $booking) => $this->clearances->toArray($booking))
            ->values()
            ->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'summary' => $this->clearances->summary($filters),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'filters' => [
                'states' => $this->clearances->stateOptions(),
                'beauticians' => $this->clearances->beauticianOptions(),
                'branches' => $this->clearances->branchOptions(),
            ],
        ]);
    }
}
