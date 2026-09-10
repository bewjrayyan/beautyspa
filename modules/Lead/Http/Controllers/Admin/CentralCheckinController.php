<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Lead\Services\CentralCheckinService;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

final class CentralCheckinController
{
    public function __construct(
        private readonly CentralCheckinService $checkins,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $branchFilter = $request->query('branch', $request->query('spa_branch_id'));

        $filters = [
            'q' => $request->query('q'),
            'status' => $request->query('status', 'live'),
            'branch' => $branchFilter,
            'beautician' => $request->query('beautician', $request->query('beautician_id')),
            'date' => $request->query('date'),
            'scope' => $request->query('scope', 'day'),
            'per_page' => (int) $request->query('per_page', 25),
        ];

        $paginator = $this->checkins->paginate($filters);

        $data = collect($paginator->items())
            ->map(fn (TreatmentBooking $booking) => $this->checkins->toArray($booking))
            ->values()
            ->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'summary' => $this->checkins->summary($filters),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'date' => $request->query('date') ?: now()->toDateString(),
            ],
            'filters' => [
                'statuses' => $this->checkins->statusOptions(),
                'beauticians' => $this->checkins->beauticianOptions(),
                'branches' => $this->checkins->branchOptions(),
            ],
        ]);
    }
}
