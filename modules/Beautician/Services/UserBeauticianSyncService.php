<?php

namespace Modules\Beautician\Services;

use Modules\Beautician\Entities\Beautician;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

class UserBeauticianSyncService
{
    /**
     * Ensure a beautician profile exists when the user has the Beautician role.
     */
    public function syncFromUser(User $user): ?Beautician
    {
        $user->loadMissing('roles');

        if (! $this->userHasBeauticianRole($user)) {
            return null;
        }

        $beautician = Beautician::query()->where('user_id', $user->id)->first();

        if ($beautician) {
            return $this->updateFromUser($beautician, $user);
        }

        return $this->createFromUser($user);
    }


    private function userHasBeauticianRole(User $user): bool
    {
        return $user->hasRoleName('Beautician');
    }


    private function createFromUser(User $user): Beautician
    {
        $nextPosition = (int) Beautician::query()->max('position') + 1;

        return Beautician::create([
            'user_id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => PhoneNumber::normalize($user->phone) ?: $user->phone,
            'profile_color' => $this->defaultProfileColor($user->id),
            'is_active' => true,
            'position' => $nextPosition,
        ]);
    }


    private function updateFromUser(Beautician $beautician, User $user): Beautician
    {
        $beautician->update([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => PhoneNumber::normalize($user->phone) ?: $user->phone,
            'is_active' => $beautician->is_active,
        ]);

        app(BeauticianPortalUserService::class)->ensureBeauticianRole($user);

        return $beautician->fresh();
    }


    private function defaultProfileColor(int $userId): string
    {
        $colors = ['#6366f1', '#ec4899', '#f274ac', '#047857', '#ea580c', '#4338ca'];

        return $colors[$userId % count($colors)];
    }
}
