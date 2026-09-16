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

        $this->ensureSessionStarted((int) $beautician->id, (int) auth()->id());
    }


    /**
     * Restore an admin's selected portal context for a subsequent request.
     *
     * Preview is intentionally limited to an admin who can already open the
     * reservation workspace. A beautician always resolves only their own
     * profile through BeauticianPortalMiddleware.
     */
    public function restoreFromSession(): void
    {
        if ($this->isActive()) {
            return;
        }

        $viewer = auth()->user();

        if (! $viewer || $viewer->isBeauticianOnly() || ! $viewer->hasAccess('admin.treatment_reservations.index')) {
            return;
        }

        $beauticianId = (int) session('admin_portal_preview_beautician_id');
        $previewerId = (int) session('admin_portal_preview_admin_user_id');

        if (! $beauticianId || ($previewerId && $previewerId !== (int) $viewer->id)) {
            return;
        }

        $beautician = Beautician::query()->with('user')->find($beauticianId);

        if (! $beautician?->user) {
            return;
        }

        $this->beautician = $beautician;
        $this->portalUser = $beautician->user;

        session(['admin_portal_preview_admin_user_id' => $viewer->id]);
    }


    public function startedAt(): ?\Carbon\CarbonInterface
    {
        $timestamp = session('admin_portal_preview_started_at');

        if (! is_numeric($timestamp)) {
            return null;
        }

        return \Carbon\Carbon::createFromTimestamp((int) $timestamp);
    }


    private function ensureSessionStarted(int $beauticianId, int $previewerId): void
    {
        if ((int) session('admin_portal_preview_beautician_id') !== $beauticianId) {
            session([
                'admin_portal_preview_started_at' => now()->timestamp,
                'admin_portal_preview_beautician_id' => $beauticianId,
                'admin_portal_preview_admin_user_id' => $previewerId,
            ]);

            return;
        }

        session(['admin_portal_preview_admin_user_id' => $previewerId]);
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
