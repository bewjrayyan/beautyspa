<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Facades\DB;
use Modules\Beautician\Entities\Beautician;

class ManualBookingSlotsResolver
{
    public function __construct(
        private AppointmentAvailabilityService $appointmentAvailability,
        private BeauticianAvailabilityService $beauticianAvailability,
    ) {}

    /**
     * @param array{
     *     beautician_id: int,
     *     date: string,
     *     booking_id?: int|null,
     *     product_id?: int|null,
     *     spa_branch_id?: int|null
     * } $data
     * @return list<string>
     */
    public function resolve(array $data): array
    {
        $beauticianId = (int) $data['beautician_id'];
        $date = $data['date'];
        $excludeBookingId = isset($data['booking_id']) ? (int) $data['booking_id'] : null;
        $productId = (int) ($data['product_id'] ?? 0);
        $spaBranchId = (int) ($data['spa_branch_id'] ?? 0);

        if (! Beautician::query()->whereKey($beauticianId)->where('is_active', true)->exists()) {
            throw new \InvalidArgumentException(trans('treatmentreservation::admin.manual_booking.beautician_inactive'));
        }

        if ($spaBranchId && ! DB::table('beautician_spa_branch')
            ->where('beautician_id', $beauticianId)
            ->where('spa_branch_id', $spaBranchId)
            ->exists()) {
            throw new \InvalidArgumentException(trans('treatmentreservation::admin.manual_booking.beautician_branch_mismatch'));
        }

        if ($productId && $spaBranchId) {
            return $this->appointmentAvailability->availableSlots(
                $productId,
                $spaBranchId,
                $date,
                $beauticianId,
                $excludeBookingId,
            );
        }

        return $this->beauticianAvailability->availableSlots($beauticianId, $date, $excludeBookingId);
    }
}
