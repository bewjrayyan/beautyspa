<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Facades\DB;
use Modules\Beautician\Entities\Beautician;
use Modules\Product\Entities\Product;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

class ManualBookingService
{
    public function __construct(
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
            $date = (string) $data['appointment_date'];
            $time = (string) $data['appointment_time'];

            $this->availability->lockAppointmentsForDate($beauticianId, $date);

            if (! $this->availability->isSlotAvailable($beauticianId, $date, $time)) {
                throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
            }

            $selection = $this->productSelection->validateAndResolve($data);

            Beautician::query()
                ->where('is_active', true)
                ->findOrFail($beauticianId);

            $normalizedTime = $this->availability->normalizeTime($time);

            if ($normalizedTime === null) {
                throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
            }

            $phone = PhoneNumber::normalize($data['customer_phone'] ?? '') ?: ($data['customer_phone'] ?? null);
            $receiptFileId = $this->paymentReceipts->store($data['payment_receipt'] ?? null);

            $booking = TreatmentBooking::create([
                'order_id' => null,
                'source' => $source,
                'created_by_user_id' => $actor->id,
                'beautician_id' => $beauticianId,
                'treatment_category_id' => $selection['product']->treatment_category_id,
                'product_id' => $selection['product']->id,
                'variant_id' => $selection['variant']?->id,
                'product_options' => $selection['options'] ?: null,
                'product_variations' => $selection['variations'] ?: null,
                'customer_first_name' => $data['customer_first_name'],
                'customer_last_name' => $data['customer_last_name'],
                'customer_phone' => $phone,
                'customer_email' => $data['customer_email'] ?? null,
                'appointment_date' => $date,
                'appointment_time' => $normalizedTime,
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
            $date = (string) $data['appointment_date'];
            $time = (string) $data['appointment_time'];

            $this->availability->lockAppointmentsForDate($beauticianId, $date);

            if (! $this->availability->isSlotAvailable($beauticianId, $date, $time, $booking->id)) {
                throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
            }

            $selection = $this->productSelection->validateAndResolve($data);

            Beautician::query()
                ->where('is_active', true)
                ->findOrFail($beauticianId);

            $normalizedTime = $this->availability->normalizeTime($time);

            if ($normalizedTime === null) {
                throw new \InvalidArgumentException(trans('treatmentreservation::public.slot_unavailable'));
            }

            $phone = PhoneNumber::normalize($data['customer_phone'] ?? '') ?: ($data['customer_phone'] ?? null);
            $receiptFileId = $this->paymentReceipts->store(
                $data['payment_receipt'] ?? null,
                $booking->payment_receipt_file_id
            );

            $changes = [
                'beautician_id' => $beauticianId,
                'treatment_category_id' => $selection['product']->treatment_category_id,
                'product_id' => $selection['product']->id,
                'variant_id' => $selection['variant']?->id,
                'product_options' => $selection['options'] ?: null,
                'product_variations' => $selection['variations'] ?: null,
                'customer_first_name' => $data['customer_first_name'],
                'customer_last_name' => $data['customer_last_name'],
                'customer_phone' => $phone,
                'customer_email' => $data['customer_email'] ?? null,
                'appointment_date' => $date,
                'appointment_time' => $normalizedTime,
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
}
