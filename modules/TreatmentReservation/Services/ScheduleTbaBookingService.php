<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Facades\DB;
use Modules\Beautician\Entities\Beautician;
use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Entities\User;

class ScheduleTbaBookingService
{
    public function __construct(
        private AppointmentAvailabilityService $availability,
        private TreatmentBookingActivityLogger $activityLogger,
        private BookingCustomerWhatsAppService $customerWhatsApp,
    ) {}


    /**
     * Assign a real calendar slot to a TBA booking and clear schedule_status.
     *
     * @param  array{appointment_date: string, appointment_time: string, beautician_id?: int}  $data
     */
    public function schedule(TreatmentBooking $booking, array $data, User $actor, bool $notifyCustomer = true): TreatmentBooking
    {
        if (! $booking->canScheduleTba()) {
            throw new \InvalidArgumentException(trans('treatmentreservation::admin.tba.not_schedulable'));
        }

        return DB::transaction(function () use ($booking, $data, $actor, $notifyCustomer) {
            $beauticianId = (int) ($data['beautician_id'] ?? $booking->beautician_id);
            $date = (string) $data['appointment_date'];
            $time = (string) $data['appointment_time'];
            $spaBranchId = (int) ($data['spa_branch_id'] ?? $booking->spa_branch_id ?? 0);
            $productId = (int) ($booking->product_id ?? 0);

            if (! Beautician::query()->whereKey($beauticianId)->where('is_active', true)->exists()) {
                throw new \InvalidArgumentException(trans('treatmentreservation::admin.manual_booking.beautician_inactive'));
            }

            if ($spaBranchId && (! DB::table('spa_branches')->where('id', $spaBranchId)->where('is_active', true)->exists()
                || ! DB::table('beautician_spa_branch')->where('beautician_id', $beauticianId)->where('spa_branch_id', $spaBranchId)->exists())) {
                throw new \InvalidArgumentException(trans('treatmentreservation::admin.manual_booking.beautician_branch_mismatch'));
            }

            if ($productId && $spaBranchId) {
                $this->availability->assertSlotBookable(
                    $productId,
                    $spaBranchId,
                    $date,
                    $time,
                    $beauticianId,
                    $booking->id
                );
            } else {
                app(BeauticianAvailabilityService::class)->lockAppointmentsForDate($beauticianId, $date);

                if (! app(BeauticianAvailabilityService::class)->isSlotAvailable($beauticianId, $date, $time, $booking->id)) {
                    throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
                }
            }

            $normalizedTime = $this->availability->normalizeTime($time);

            if ($normalizedTime === null) {
                throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
            }

            $booking->update([
                'beautician_id' => $beauticianId,
                'spa_branch_id' => $spaBranchId ?: $booking->spa_branch_id,
                'appointment_date' => $date,
                'appointment_time' => $normalizedTime,
                'checked_in_at' => null,
                'customer_reminder_sent_at' => null,
                'customer_email_reminder_sent_at' => null,
                'duration_minutes_snapshot' => $productId && $spaBranchId
                    ? $this->availability->resolveDurationMinutes($productId, $spaBranchId)
                    : $booking->duration_minutes_snapshot,
                'schedule_status' => null,
            ]);

            if ($booking->order_id) {
                $order = Order::query()->find($booking->order_id);

                if ($order) {
                    app(BookingSyncService::class)->refreshOrderAppointmentSnapshot($order);
                }
            }

            $this->activityLogger->logUpdated($booking, $actor->id);

            $fresh = $booking->fresh(['beautician.files', 'product', 'category', 'order']);

            if ($notifyCustomer && $fresh && $this->customerWhatsApp->canSend($fresh)) {
                try {
                    $this->customerWhatsApp->send(
                        $fresh,
                        $this->confirmationMessage($fresh)
                    );
                } catch (\Throwable) {
                    // Scheduling succeeded even if WhatsApp delivery fails.
                }
            }

            return $fresh;
        });
    }


    private function confirmationMessage(TreatmentBooking $booking): string
    {
        $booking->loadMissing(['beautician', 'product']);

        $store = setting('store_name');
        $customer = $booking->customer_full_name ?: 'Pelanggan';
        $treatment = $booking->product?->name ?: '—';
        $date = $booking->appointment_date?->format('d M Y') ?: '—';
        $time = $booking->displayAppointmentTime() ?: '—';
        $beautician = $booking->beautician?->name;

        $lines = [
            "Hai {$customer},",
            '',
            "Ini *{$store}*. Tempahan rawatan anda telah dijadualkan:",
            '',
            "Rawatan: {$treatment}",
            "Tarikh: {$date}",
            "Masa: {$time}",
        ];

        if ($beautician) {
            $lines[] = "Beautician: {$beautician}";
        }

        $lines[] = '';
        $lines[] = 'Sila datang tepat pada masa. Terima kasih!';

        return implode("\n", $lines);
    }
}
