<?php

namespace Modules\TreatmentReservation\Services;

use Modules\Beautician\Entities\Beautician;
use Modules\User\Entities\User;

class AdminPortalPreview
{
    private ?Beautician $beautician = null;

    private ?User $portalUser = null;


    public function activate(Beautician $beautician): void
    {
        $this->beautician = $beautician->loadMissing('user');

        $this->portalUser = $this->beautician->user;
    }


    public function isActive(): bool
    {
        return $this->portalUser !== null;
    }


    public function beautician(): ?Beautician
    {
        return $this->beautician;
    }


    public function portalUser(): ?User
    {
        return $this->portalUser;
    }


    /**
     * User whose role permissions should drive the admin sidebar in preview mode.
     */
    public function effectiveUser(): ?User
    {
        return $this->portalUser;
    }


    public function homeRoute(): ?string
    {
        if (! $this->beautician) {
            return null;
        }

        return route('admin.beauticians.portal.dashboard', $this->beautician->id);
    }
}
