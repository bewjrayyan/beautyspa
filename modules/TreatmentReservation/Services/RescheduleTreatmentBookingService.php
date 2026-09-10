<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Facades\DB;
use Modules\Beautician\Entities\Beautician;
use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Entities\User;

class RescheduleTreatmentBookingService
{
    public function __construct(
        private AppointmentAvailabilityService $availability,
        private TreatmentBookingActivityLogger $activityLogger,
        private BookingCustomerWhatsAppService $customerWhatsApp,
        private BeauticianBookingNotificationService $beauticianWhatsApp,
    ) {}


    /**
     * @param  array{appointment_date: string, appointment_time: string}  $data
     * @return array{booking: TreatmentBooking, customer_notified: bool, beautician_notified: bool}
     */
    public function reschedule(
        TreatmentBooking $booking,
        array $data,
        User $actor,
        bool $notifyCustomer = true,
        bool $notifyBeautician = true,
    ): array {
        $change = DB::transaction(function () use ($booking, $data, $actor) {
            $locked = TreatmentBooking::query()
                ->with('order')
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if (! $locked->canRescheduleAppointment()) {
                throw new \InvalidArgumentException(trans('treatmentreservation::admin.reschedule.not_allowed'));
            }

            $beauticianId = (int) $locked->beautician_id;
            $branchId = (int) ($locked->spa_branch_id ?? $locked->order?->spa_branch_id ?? 0);
            $productId = (int) ($locked->product_id ?? 0);
            $date = (string) $data['appointment_date'];
            $time = (string) $data['appointment_time'];

            if (! $beauticianId || ! Beautician::query()->whereKey($beauticianId)->where('is_active', true)->exists()) {
                throw new \InvalidArgumentException(trans('treatmentreservation::admin.manual_booking.beautician_inactive'));
            }

            if ($branchId && ! DB::table('beautician_spa_branch')
                ->where('beautician_id', $beauticianId)
                ->where('spa_branch_id', $branchId)
                ->exists()) {
                throw new \InvalidArgumentException(trans('treatmentreservation::admin.manual_booking.beautician_branch_mismatch'));
            }

            if ($productId && $branchId) {
                $this->availability->assertSlotBookable(
                    $productId,
                    $branchId,
                    $date,
                    $time,
                    $beauticianId,
                    $locked->id,
                );
            } else {
                app(BeauticianAvailabilityService::class)->lockAppointmentsForDate($beauticianId, $date);

                if (! app(BeauticianAvailabilityService::class)->isSlotAvailable($beauticianId, $date, $time, $locked->id)) {
                    throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
                }
            }

            $normalizedTime = $this->availability->normalizeTime($time);

            if ($normalizedTime === null) {
                throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
            }

            $oldDate = $locked->appointment_date?->format('Y-m-d');
            $oldTime = $locked->displayAppointmentTime();

            $locked->update([
                'appointment_date' => $date,
                'appointment_time' => $normalizedTime,
                'customer_reminder_sent_at' => null,
                'customer_email_reminder_sent_at' => null,
                'duration_minutes_snapshot' => $productId && $branchId
                    ? $this->availability->resolveDurationMinutes($productId, $branchId)
                    : $locked->duration_minutes_snapshot,
            ]);

            if ($locked->order_id && ($order = Order::query()->find($locked->order_id))) {
                app(BookingSyncService::class)->refreshOrderAppointmentSnapshot($order);
            }

            $this->activityLogger->logUpdated($locked, $actor->id);

            return [
                'booking' => $locked->fresh(['beautician.files', 'product', 'category', 'order']),
                'old_date' => $oldDate,
                'old_time' => $oldTime,
            ];
        });

        $fresh = $change['booking'];
        $customerNotified = false;
        $beauticianNotified = false;

        if ($notifyCustomer) {
            $customerNotified = $this->customerWhatsApp->notifyRescheduled(
                $fresh,
                $change['old_date'],
                $change['old_time'],
            );
        }

        if ($notifyBeautician) {
            $beauticianNotified = $this->beauticianWhatsApp->notifyRescheduledBooking(
                $fresh,
                $change['old_date'],
                $change['old_time'],
            );
        }

        return [
            'booking' => $fresh,
            'customer_notified' => $customerNotified,
            'beautician_notified' => $beauticianNotified,
        ];
    }
}
