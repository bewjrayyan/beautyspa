<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Facades\DB;
use Modules\Beautician\Entities\Beautician;
use Modules\Product\Entities\Product;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Entities\TreatmentBranchAvailability;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

class ManualBookingService
{
    public function __construct(
        private AppointmentAvailabilityService $appointmentAvailability,
        private BeauticianAvailabilityService $availability,
        private TreatmentBookingActivityLogger $activityLogger,
        private ManualBookingProductSelectionValidator $productSelection,
        private ManualBookingPaymentReceiptService $paymentReceipts,
    ) {}


    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data, User $actor, string $source = TreatmentBooking::SOURCE_ADMIN_MANUAL): TreatmentBooking
    {
        return DB::transaction(function () use ($data, $actor, $source) {
            $beauticianId = (int) $data['beautician_id'];
            $scheduleLater = filter_var($data['schedule_later'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $date = $scheduleLater ? null : (string) ($data['appointment_date'] ?? '');
            $time = $scheduleLater ? null : (string) ($data['appointment_time'] ?? '');
            $normalizedTime = null;
            $spaBranchId = (int) ($data['spa_branch_id'] ?? 0);

            $selection = $this->productSelection->validateAndResolve($data);
            $this->assertBookingContext(
                $beauticianId,
                $spaBranchId,
                (int) $selection['product']->id,
                $scheduleLater
            );

            if (! $scheduleLater) {
                $productId = (int) $selection['product']->id;

                if ($productId && $spaBranchId) {
                    $this->appointmentAvailability->assertSlotBookable(
                        $productId,
                        $spaBranchId,
                        $date,
                        $time,
                        $beauticianId
                    );
                } else {
                    $this->availability->lockAppointmentsForDate($beauticianId, $date);

                    if (! $this->availability->isSlotAvailable($beauticianId, $date, $time)) {
                        throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
                    }
                }

                $normalizedTime = $this->availability->normalizeTime($time);

                if ($normalizedTime === null) {
                    throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
                }
            }

            $phone = PhoneNumber::normalize($data['customer_phone'] ?? '') ?: ($data['customer_phone'] ?? null);
            $receiptFileId = $this->paymentReceipts->store($data['payment_receipt'] ?? null);

            $booking = TreatmentBooking::create([
                'order_id' => null,
                'source' => $source,
                'created_by_user_id' => $actor->id,
                'beautician_id' => $beauticianId,
                'spa_branch_id' => $spaBranchId ?: null,
                'treatment_category_id' => $selection['product']->treatment_category_id,
                'product_id' => $selection['product']->id,
                'variant_id' => $selection['variant']?->id,
                'product_options' => $selection['options'] ?: null,
                'product_variations' => $selection['variations'] ?: null,
                'customer_first_name' => $data['customer_first_name'],
                'customer_last_name' => $data['customer_last_name'],
                'customer_phone' => $phone,
                'customer_email' => $data['customer_email'] ?? null,
                'appointment_date' => $scheduleLater ? null : $date,
                'appointment_time' => $normalizedTime,
                'duration_minutes_snapshot' => $spaBranchId
                    ? $this->appointmentAvailability->resolveDurationMinutes((int) $selection['product']->id, $spaBranchId)
                    : null,
                'schedule_status' => $scheduleLater ? TreatmentBooking::SCHEDULE_STATUS_TBA : null,
                'status' => TreatmentBooking::STATUS_PENDING,
                'total' => $selection['total'],
                'currency' => currency(),
                'payment_status' => $data['payment_status'] ?? TreatmentBooking::PAYMENT_DEPOSIT,
                'payment_receipt_file_id' => $receiptFileId,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->activityLogger->logCreated($booking, $actor->id);

            return $booking->fresh(['beautician.files', 'product', 'category', 'paymentReceipt']);
        });
    }


    public function isEditable(TreatmentBooking $booking): bool
    {
        return $booking->isManualEditable();
    }


    /**
     * @param array<string, mixed> $data
     */
    public function update(TreatmentBooking $booking, array $data, User $actor, bool $allowBeauticianChange = true): TreatmentBooking
    {
        if (! $this->isEditable($booking)) {
            throw new \InvalidArgumentException(trans('treatmentreservation::admin.manual_booking.not_editable'));
        }

        return DB::transaction(function () use ($booking, $data, $actor, $allowBeauticianChange) {
            $beauticianId = $allowBeauticianChange
                ? (int) ($data['beautician_id'] ?? $booking->beautician_id)
                : (int) $booking->beautician_id;
            $scheduleLater = filter_var($data['schedule_later'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $date = $scheduleLater ? null : (string) ($data['appointment_date'] ?? '');
            $time = $scheduleLater ? null : (string) ($data['appointment_time'] ?? '');
            $normalizedTime = null;
            $selection = $this->productSelection->validateAndResolve($data);
            $spaBranchId = (int) ($data['spa_branch_id'] ?? $booking->spa_branch_id ?? 0);
            $this->assertBookingContext(
                $beauticianId,
                $spaBranchId,
                (int) $selection['product']->id,
                $scheduleLater
            );

            if (! $scheduleLater) {
                $productId = (int) $selection['product']->id;

                if ($productId && $spaBranchId) {
                    $this->appointmentAvailability->assertSlotBookable(
                        $productId,
                        $spaBranchId,
                        $date,
                        $time,
                        $beauticianId,
                        $booking->id
                    );
                } else {
                    $this->availability->lockAppointmentsForDate($beauticianId, $date);

                    if (! $this->availability->isSlotAvailable($beauticianId, $date, $time, $booking->id)) {
                        throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
                    }
                }

                $normalizedTime = $this->availability->normalizeTime($time);

                if ($normalizedTime === null) {
                    throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
                }
            }

            $phone = PhoneNumber::normalize($data['customer_phone'] ?? '') ?: ($data['customer_phone'] ?? null);
            $receiptFileId = $this->paymentReceipts->store(
                $data['payment_receipt'] ?? null,
                $booking->payment_receipt_file_id
            );

            $changes = [
                'beautician_id' => $beauticianId,
                'spa_branch_id' => $spaBranchId ?: $booking->spa_branch_id,
                'treatment_category_id' => $selection['product']->treatment_category_id,
                'product_id' => $selection['product']->id,
                'variant_id' => $selection['variant']?->id,
                'product_options' => $selection['options'] ?: null,
                'product_variations' => $selection['variations'] ?: null,
                'customer_first_name' => $data['customer_first_name'],
                'customer_last_name' => $data['customer_last_name'],
                'customer_phone' => $phone,
                'customer_email' => $data['customer_email'] ?? null,
                'appointment_date' => $scheduleLater ? null : $date,
                'appointment_time' => $normalizedTime,
                'duration_minutes_snapshot' => $spaBranchId
                    ? $this->appointmentAvailability->resolveDurationMinutes((int) $selection['product']->id, $spaBranchId)
                    : $booking->duration_minutes_snapshot,
                'schedule_status' => $scheduleLater ? TreatmentBooking::SCHEDULE_STATUS_TBA : null,
                'total' => $selection['total'],
                'payment_status' => $data['payment_status']
                    ?? TreatmentBooking::normalizeManualPaymentStatus($booking->payment_status),
                'payment_receipt_file_id' => $receiptFileId,
                'notes' => $data['notes'] ?? null,
            ];

            $booking->update($changes);

            $this->activityLogger->logUpdated($booking, $actor->id);

            return $booking->fresh(['beautician.files', 'product', 'category', 'paymentReceipt']);
        });
    }


    public function cancel(TreatmentBooking $booking, User $actor): TreatmentBooking
    {
        if (! $this->isEditable($booking)) {
            throw new \InvalidArgumentException(trans('treatmentreservation::admin.manual_booking.not_editable'));
        }

        $previousStatus = $booking->status;

        $booking->update(['status' => TreatmentBooking::STATUS_CANCELED]);

        $this->activityLogger->logStatusChange($booking, $previousStatus, TreatmentBooking::STATUS_CANCELED);

        return $booking->fresh(['beautician.files', 'product', 'category', 'paymentReceipt']);
    }


    private function assertBookingContext(int $beauticianId, int $spaBranchId, int $productId, bool $scheduleLater): void
    {
        if (! Beautician::query()->whereKey($beauticianId)->where('is_active', true)->exists()) {
            throw new \InvalidArgumentException(trans('treatmentreservation::admin.manual_booking.beautician_inactive'));
        }

        if (! $spaBranchId) {
            return;
        }

        if (! DB::table('spa_branches')->where('id', $spaBranchId)->where('is_active', true)->exists()
            || ! DB::table('beautician_spa_branch')->where('beautician_id', $beauticianId)->where('spa_branch_id', $spaBranchId)->exists()) {
            throw new \InvalidArgumentException(trans('treatmentreservation::admin.manual_booking.beautician_branch_mismatch'));
        }

        $settings = TreatmentBranchAvailability::query()
            ->where('product_id', $productId)
            ->where('spa_branch_id', $spaBranchId)
            ->first();

        if ($settings && (! $settings->is_bookable || ($scheduleLater && ! $settings->allow_tba))) {
            throw new \InvalidArgumentException(trans('treatmentreservation::admin.manual_booking.treatment_not_bookable'));
        }
    }
}
