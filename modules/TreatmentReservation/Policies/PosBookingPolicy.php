<?php

namespace Modules\TreatmentReservation\Policies;

use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Entities\User;

class PosBookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isStaff($user) && ($this->isAdminReader($user) || $user->isBeauticianOnly());
    }

    public function view(User $user, TreatmentBooking $booking): bool
    {
        return $this->isAdminReader($user) || $this->owns($user, $booking);
    }

    public function create(User $user): bool
    {
        return $this->isAdminCreator($user) || $this->isPortalBeautician($user);
    }

    public function update(User $user, TreatmentBooking $booking): bool
    {
        return $this->isAdminEditor($user) || $this->owns($user, $booking);
    }

    public function delete(User $user, TreatmentBooking $booking): bool
    {
        return $this->update($user, $booking);
    }

    private function isStaff(User $user): bool
    {
        return $user->hasRoleName('admin') || $user->isBeauticianOnly();
    }

    private function isAdminReader(User $user): bool
    {
        return $user->hasAccess('admin.treatment_reservations.index');
    }

    private function isAdminCreator(User $user): bool
    {
        return $user->hasAccess('admin.treatment_reservations.create');
    }

    private function isAdminEditor(User $user): bool
    {
        return $user->hasAccess('admin.treatment_reservations.edit');
    }

    private function isPortalBeautician(User $user): bool
    {
        return $user->isBeauticianOnly()
            && $user->hasAccess('admin.treatment_reservations.portal.create');
    }

    private function owns(User $user, TreatmentBooking $booking): bool
    {
        $profile = $user->beauticianProfile;

        return $profile !== null && (int) $booking->beautician_id === (int) $profile->id;
    }
}
