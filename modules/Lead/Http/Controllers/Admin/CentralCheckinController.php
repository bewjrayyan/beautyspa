<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Controllers\Admin;

use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Lead\Services\CentralCheckinService;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Services\BookingCheckinPassService;
use Modules\TreatmentReservation\Services\TreatmentBookingActivityLogger;

final class CentralCheckinController
{
    public function __construct(
        private readonly CentralCheckinService $checkins,
        private readonly BookingCheckinPassService $passes,
        private readonly TreatmentBookingActivityLogger $activities,
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

    public function confirm(Request $request, int $booking): JsonResponse|RedirectResponse
    {
        try {
            $checkedIn = DB::transaction(function () use ($booking, $request): TreatmentBooking {
                /** @var TreatmentBooking $record */
                $record = TreatmentBooking::query()->lockForUpdate()->findOrFail($booking);

                if ($record->checked_in_at) {
                    return $record;
                }

                if ($record->status !== TreatmentBooking::STATUS_PENDING) {
                    throw new DomainException(trans('lead::central.checkin.checkin_status_invalid'));
                }

                if (! $record->appointment_date || ! $record->appointment_date->isToday()) {
                    throw new DomainException(trans('lead::central.checkin.checkin_date_invalid'));
                }

                $record->forceFill(['checked_in_at' => now()])->save();
                $this->activities->logCheckin($record, $request->user()?->id);

                return $record->fresh();
            });
        } catch (DomainException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()->withErrors(['checkin' => $exception->getMessage()]);
        }

        $message = trans('lead::central.checkin.checkin_confirmed', [
            'code' => $checkedIn->referenceCode(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'data' => $this->checkins->toArray($checkedIn),
            ]);
        }

        return redirect($this->passes->url($checkedIn))->with('checkin_success', $message);
    }
}
