<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Controllers\Admin;

use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

    public function updateStatus(Request $request, int $booking): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in([
                TreatmentBooking::STATUS_IN_PROGRESS,
                TreatmentBooking::STATUS_COMPLETED,
            ])],
        ]);

        try {
            $updated = $this->clearances->transition(
                $booking,
                $validated['status'],
                $request->user()?->getAuthIdentifier() ? (int) $request->user()->getAuthIdentifier() : null,
            );
        } catch (DomainException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => trans('lead::central.clearance.status_updated'),
            'data' => $this->clearances->toArray($updated),
        ]);
    }
}
