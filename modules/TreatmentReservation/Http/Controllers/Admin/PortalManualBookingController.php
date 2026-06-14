<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Http\Requests\StorePortalManualBookingRequest;
use Modules\TreatmentReservation\Http\Requests\UpdatePortalManualBookingRequest;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;
use Modules\TreatmentReservation\Services\CustomerLookupService;
use Modules\TreatmentReservation\Services\ManualBookingService;
use Modules\User\Entities\User;

class PortalManualBookingController extends Controller
{
    public function __construct(
        private BeauticianAvailabilityService $availability,
        private ManualBookingService $manualBookings,
        private CustomerLookupService $customerLookup,
    ) {}


    public function slots(Request $request): JsonResponse
    {
        $data = $request->validate([
            'beautician_id' => [
                'required',
                'integer',
                Rule::exists('beauticians', 'id')->where('is_active', true),
            ],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'booking_id' => ['nullable', 'integer'],
        ]);

        $excludeBookingId = isset($data['booking_id']) ? (int) $data['booking_id'] : null;

        return response()->json([
            'slots' => $this->availability->availableSlots(
                (int) $data['beautician_id'],
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
        try {
            $booking = $this->manualBookings->create(
                array_merge($request->validated(), [
                    'payment_receipt' => $request->file('payment_receipt'),
                    'options' => $request->input('options', []),
                    'variations' => $request->input('variations', []),
                ]),
                $request->user(),
                TreatmentBooking::SOURCE_PORTAL_MANUAL,
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
                'redirect' => route('admin.treatment_reservations.portal', ['view' => 'kanban']),
            ],
        ]);
    }


    public function update(UpdatePortalManualBookingRequest $request, TreatmentBooking $booking): JsonResponse
    {
        $this->assertCanManagePortalBooking($booking, $request);

        try {
            $booking = $this->manualBookings->update(
                $booking,
                array_merge($request->validated(), [
                    'payment_receipt' => $request->file('payment_receipt'),
                    'options' => $request->input('options', []),
                    'variations' => $request->input('variations', []),
                ]),
                $request->user(),
                allowBeauticianChange: true,
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


    public function cancel(Request $request, TreatmentBooking $booking): JsonResponse
    {
        $this->assertCanManagePortalBooking($booking, $request);

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


    private function assertCanManagePortalBooking(TreatmentBooking $booking, Request $request): void
    {
        /** @var Beautician $portalBeautician */
        $portalBeautician = $request->attributes->get('portal_beautician');
        /** @var User $user */
        $user = $request->user();

        $ownsBooking = (int) $booking->beautician_id === (int) $portalBeautician->id;
        $createdBooking = (int) $booking->created_by_user_id === (int) $user->id;

        if (! $ownsBooking && ! $createdBooking) {
            abort(403);
        }
    }
}
