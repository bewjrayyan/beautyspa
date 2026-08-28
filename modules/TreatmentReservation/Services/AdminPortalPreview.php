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

        $this->ensureSessionStarted((int) $beautician->id);
    }


    public function startedAt(): ?\Carbon\CarbonInterface
    {
        $timestamp = session('admin_portal_preview_started_at');

        if (! is_numeric($timestamp)) {
            return null;
        }

        return \Carbon\Carbon::createFromTimestamp((int) $timestamp);
    }


    private function ensureSessionStarted(int $beauticianId): void
    {
        if ((int) session('admin_portal_preview_beautician_id') !== $beauticianId) {
            session([
                'admin_portal_preview_started_at' => now()->timestamp,
                'admin_portal_preview_beautician_id' => $beauticianId,
            ]);
        }
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
