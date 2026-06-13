<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Http\Requests\StorePortalManualBookingRequest;
use Modules\TreatmentReservation\Http\Requests\UpdatePortalManualBookingRequest;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;
use Modules\TreatmentReservation\Services\CustomerLookupService;
use Modules\TreatmentReservation\Services\ManualBookingService;

class PortalManualBookingController extends Controller
{
    public function __construct(
        private BeauticianAvailabilityService $availability,
        private ManualBookingService $manualBookings,
        private CustomerLookupService $customerLookup,
    ) {}


    public function slots(Request $request): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'booking_id' => ['nullable', 'integer'],
        ]);

        $excludeBookingId = isset($data['booking_id']) ? (int) $data['booking_id'] : null;

        return response()->json([
            'slots' => $this->availability->availableSlots(
                $beautician->id,
                $data['date'],
                $excludeBookingId
            ),
        ]);
    }


    public function customers(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:3', 'max:50'],
        ]);

        return response()->json([
            'customers' => $this->customerLookup->search($data['q']),
        ]);
    }


    public function store(StorePortalManualBookingRequest $request): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        $payload = array_merge($request->validated(), [
            'beautician_id' => $beautician->id,
        ]);

        try {
            $booking = $this->manualBookings->create(
                $payload,
                $request->user(),
                TreatmentBooking::SOURCE_PORTAL_MANUAL,
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => trans('treatmentreservation::admin.manual_booking.created'),
            'booking' => [
                'id' => $booking->id,
                'redirect' => route('admin.treatment_reservations.portal', ['view' => 'kanban']),
            ],
        ]);
    }


    public function update(UpdatePortalManualBookingRequest $request, TreatmentBooking $booking): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        if ((int) $booking->beautician_id !== (int) $beautician->id) {
            abort(403);
        }

        $payload = array_merge($request->validated(), [
            'beautician_id' => $beautician->id,
        ]);

        try {
            $booking = $this->manualBookings->update(
                $booking,
                $payload,
                $request->user(),
                allowBeauticianChange: false,
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        $freshBooking = $booking->fresh(['beautician.files', 'product', 'category']);

        return response()->json([
            'message' => trans('treatmentreservation::admin.manual_booking.updated'),
            'booking' => $freshBooking->appendAdminPayload($freshBooking->toKanbanPayload()),
        ]);
    }


    public function cancel(Request $request, TreatmentBooking $booking): JsonResponse
    {
        /** @var Beautician $beautician */
        $beautician = $request->attributes->get('portal_beautician');

        if ((int) $booking->beautician_id !== (int) $beautician->id) {
            abort(403);
        }

        try {
            $booking = $this->manualBookings->cancel($booking, $request->user());
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        $freshBooking = $booking->fresh(['beautician.files', 'product', 'category']);

        return response()->json([
            'message' => trans('treatmentreservation::admin.manual_booking.canceled'),
            'booking' => $freshBooking->appendAdminPayload($freshBooking->toKanbanPayload()),
        ]);
    }
}
