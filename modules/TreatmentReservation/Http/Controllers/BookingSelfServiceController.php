<?php

namespace Modules\TreatmentReservation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\TreatmentReservation\Services\BookingLookupOtpService;
use Modules\TreatmentReservation\Services\BookingSelfService;
use Modules\TreatmentReservation\Services\BeauticianAvailabilityService;

class BookingSelfServiceController extends Controller
{
    public function __construct(
        private BookingLookupOtpService $otp,
        private BookingSelfService $selfService,
        private BeauticianAvailabilityService $availability
    ) {}


    public function index(): View
    {
        $verifiedPhone = $this->otp->verifiedPhone();
        $bookings = $verifiedPhone
            ? $this->selfService->upcomingForPhone($verifiedPhone)
            : collect();

        return view('treatmentreservation::public.booking.index', [
            'verifiedPhone' => $verifiedPhone,
            'bookings' => $bookings,
        ]);
    }


    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ]);

        try {
            $this->otp->send($request->input('phone'));

            return response()->json(['message' => trans('treatmentreservation::public.otp_sent')]);
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }


    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        try {
            $normalized = $this->otp->verify($request->input('phone'), $request->input('otp'));
            session(['booking_lookup_phone' => $normalized]);

            return response()->json(['message' => trans('treatmentreservation::public.verified')]);
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }


    public function logout(): RedirectResponse
    {
        $phone = session('booking_lookup_phone');

        if (is_string($phone) && $phone !== '') {
            $this->otp->forgetVerified($phone);
        }

        session()->forget('booking_lookup_phone');

        return redirect()->route('treatment_reservations.booking.lookup');
    }


    public function cancel(Request $request, int $id): JsonResponse
    {
        $verifiedPhone = $this->otp->verifiedPhone();

        if (! $verifiedPhone) {
            return response()->json(['message' => trans('treatmentreservation::public.session_expired')], 401);
        }

        $booking = $this->selfService->findOwnedBooking($verifiedPhone, $id);

        if (! $booking) {
            return response()->json(['message' => trans('treatmentreservation::public.booking_not_found')], 404);
        }

        $this->selfService->cancel($booking);

        return response()->json(['message' => trans('treatmentreservation::public.canceled')]);
    }


    public function reschedule(Request $request, int $id): JsonResponse
    {
        $verifiedPhone = $this->otp->verifiedPhone();

        if (! $verifiedPhone) {
            return response()->json(['message' => trans('treatmentreservation::public.session_expired')], 401);
        }

        $request->validate([
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'string', 'max:20'],
        ]);

        $booking = $this->selfService->findOwnedBooking($verifiedPhone, $id);

        if (! $booking) {
            return response()->json(['message' => trans('treatmentreservation::public.booking_not_found')], 404);
        }

        try {
            $this->selfService->reschedule(
                $booking,
                $request->input('appointment_date'),
                $request->input('appointment_time')
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['message' => trans('treatmentreservation::public.rescheduled')]);
    }


    public function availableSlots(Request $request, int $id): JsonResponse
    {
        $verifiedPhone = $this->otp->verifiedPhone();

        if (! $verifiedPhone) {
            return response()->json(['message' => trans('treatmentreservation::public.session_expired')], 401);
        }

        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $booking = $this->selfService->findOwnedBooking($verifiedPhone, $id);

        if (! $booking || ! $booking->beautician_id) {
            return response()->json(['message' => trans('treatmentreservation::public.booking_not_found')], 404);
        }

        return response()->json([
            'slots' => $this->availability->availableSlots(
                $booking->beautician_id,
                $request->input('date'),
                $booking->id
            ),
        ]);
    }
}
