<?php

namespace Modules\TreatmentReservation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Services\BookingCheckinPassService;
use Modules\TreatmentReservation\Services\BookingLookupOtpService;
use Modules\TreatmentReservation\Services\BookingSelfService;
use Modules\User\Entities\User;

class BookingSelfServiceController extends Controller
{
    public function __construct(
        private BookingLookupOtpService $otp,
        private BookingSelfService $selfService,
        private BookingCheckinPassService $checkinPasses,
    ) {
    }


    public function index(): View
    {
        $customer = $this->currentCustomer();
        $verifiedPhone = $customer
            ? (string) $customer->phone
            : $this->otp->verifiedPhone();
        $hasBookingAccess = $customer !== null || filled($verifiedPhone);
        $bookings = $customer
            ? $this->selfService->upcomingForCustomer($customer)
            : ($verifiedPhone ? $this->selfService->upcomingForPhone($verifiedPhone) : collect());
        $bookingGroups = $bookings
            ->groupBy(fn ($booking) => $booking->order_id
                ? 'order:' . $booking->order_id
                : 'booking:' . $booking->id)
            ->map(function ($appointments) {
                $appointments = $appointments->values();
                $firstAppointment = $appointments->first();
                $order = $firstAppointment?->order;

                return [
                    'order' => $order,
                    'appointments' => $appointments,
                    'appointment_count' => $appointments->count(),
                    'scheduled_count' => $appointments
                        ->reject(fn ($appointment) => $appointment->isTbaSchedule())
                        ->count(),
                    'created_at' => $order?->created_at ?? $firstAppointment?->created_at,
                    'payment_status' => $order?->payment_status
                        ?? $firstAppointment?->resolvedPaymentStatus(),
                    'payment_label' => $order?->paymentStatusLabel()
                        ?? $firstAppointment?->paymentStatusLabel(),
                ];
            })
            ->values();

        return view('treatmentreservation::public.booking.index', [
            'verifiedPhone' => $verifiedPhone,
            'hasBookingAccess' => $hasBookingAccess,
            'usingAccountAccess' => $customer !== null,
            'bookings' => $bookings,
            'bookingGroups' => $bookingGroups,
            'checkinPassUrls' => $bookings->mapWithKeys(fn (TreatmentBooking $booking) => [
                $booking->id => $this->checkinPasses->url($booking),
            ]),
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
            report($exception);

            return response()->json([
                'message' => trans('treatmentreservation::public.otp_send_failed'),
            ], 422);
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
            report($exception);

            return response()->json([
                'message' => trans('treatmentreservation::public.otp_verification_failed'),
            ], 422);
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
        if (! $this->hasBookingAccess()) {
            return response()->json(['message' => trans('treatmentreservation::public.session_expired')], 401);
        }

        $booking = $this->findAccessibleBooking($id);

        if (! $booking) {
            return response()->json(['message' => trans('treatmentreservation::public.booking_not_found')], 404);
        }

        try {
            $this->selfService->cancel($booking);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['message' => trans('treatmentreservation::public.canceled')]);
    }


    public function reschedule(Request $request, int $id): JsonResponse
    {
        if (! $this->hasBookingAccess()) {
            return response()->json(['message' => trans('treatmentreservation::public.session_expired')], 401);
        }

        $request->validate([
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'string', 'max:20'],
        ]);

        $booking = $this->findAccessibleBooking($id);

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
        if (! $this->hasBookingAccess()) {
            return response()->json(['message' => trans('treatmentreservation::public.session_expired')], 401);
        }

        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $booking = $this->findAccessibleBooking($id);

        if (! $booking || ! $booking->beautician_id) {
            return response()->json(['message' => trans('treatmentreservation::public.booking_not_found')], 404);
        }

        return response()->json([
            'slots' => $this->selfService->availableSlotsForBooking(
                $booking,
                $request->input('date')
            ),
        ]);
    }


    public function availableDates(Request $request, int $id): JsonResponse
    {
        if (! $this->hasBookingAccess()) {
            return response()->json(['message' => trans('treatmentreservation::public.session_expired')], 401);
        }

        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $booking = $this->findAccessibleBooking($id);

        if (! $booking || ! $booking->beautician_id) {
            return response()->json(['message' => trans('treatmentreservation::public.booking_not_found')], 404);
        }

        $from = $request->input('from') ?: today()->toDateString();
        $to = $request->input('to') ?: Carbon::parse($from)->addMonths(2)->toDateString();

        return response()->json([
            'dates' => $this->selfService->availableDatesForBooking($booking, $from, $to),
        ]);
    }


    private function currentCustomer(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }


    private function hasBookingAccess(): bool
    {
        return $this->currentCustomer() !== null || filled($this->otp->verifiedPhone());
    }


    private function findAccessibleBooking(int $bookingId): ?TreatmentBooking
    {
        $customer = $this->currentCustomer();

        if ($customer) {
            return $this->selfService->findOwnedBookingForCustomer($customer, $bookingId);
        }

        $verifiedPhone = $this->otp->verifiedPhone();

        return $verifiedPhone
            ? $this->selfService->findOwnedBooking($verifiedPhone, $bookingId)
            : null;
    }
}
