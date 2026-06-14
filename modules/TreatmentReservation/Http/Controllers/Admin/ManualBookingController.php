<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Http\Requests\StoreManualBookingRequest;
use Modules\TreatmentReservation\Http\Requests\UpdateManualBookingRequest;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;
use Modules\TreatmentReservation\Services\CustomerLookupService;
use Modules\TreatmentReservation\Services\ManualBookingService;

class ManualBookingController extends Controller
{
    public function __construct(
        private BeauticianAvailabilityService $availability,
        private ManualBookingService $manualBookings,
        private CustomerLookupService $customerLookup,
    ) {}


    public function slots(): JsonResponse
    {
        $data = request()->validate([
            'beautician_id' => ['required', 'integer', 'exists:beauticians,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'booking_id' => ['nullable', 'integer'],
        ]);

        $beautician = Beautician::query()
            ->where('id', $data['beautician_id'])
            ->where('is_active', true)
            ->first();

        if (! $beautician) {
            return response()->json([
                'message' => trans('treatmentreservation::admin.manual_booking.beautician_inactive'),
            ], 422);
        }

        $excludeBookingId = isset($data['booking_id']) ? (int) $data['booking_id'] : null;

        return response()->json([
            'slots' => $this->availability->availableSlots(
                (int) $data['beautician_id'],
                $data['date'],
                $excludeBookingId
            ),
        ]);
    }


    public function customers(): JsonResponse
    {
        $data = request()->validate([
            'q' => ['required', 'string', 'min:3', 'max:50'],
        ]);

        return response()->json([
            'customers' => $this->customerLookup->search($data['q']),
        ]);
    }


    public function store(StoreManualBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->manualBookings->create(
                array_merge($request->validated(), [
                    'payment_receipt' => $request->file('payment_receipt'),
                    'options' => $request->input('options', []),
                    'variations' => $request->input('variations', []),
                ]),
                $request->user(),
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => trans('treatmentreservation::admin.manual_booking.created'),
            'booking' => [
                'id' => $booking->id,
                'redirect' => route('admin.treatment_reservations.index', ['view' => 'kanban']),
            ],
        ]);
    }


    public function update(UpdateManualBookingRequest $request, TreatmentBooking $booking): JsonResponse
    {
        try {
            $booking = $this->manualBookings->update(
                $booking,
                array_merge($request->validated(), [
                    'payment_receipt' => $request->file('payment_receipt'),
                    'options' => $request->input('options', []),
                    'variations' => $request->input('variations', []),
                ]),
                $request->user(),
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        $freshBooking = $booking->fresh(['beautician.files', 'product', 'category', 'paymentReceipt']);

        return response()->json([
            'message' => trans('treatmentreservation::admin.manual_booking.updated'),
            'booking' => $freshBooking->appendAdminPayload($freshBooking->toKanbanPayload()),
        ]);
    }


    public function cancel(TreatmentBooking $booking): JsonResponse
    {
        try {
            $booking = $this->manualBookings->cancel($booking, request()->user());
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
