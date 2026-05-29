<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Facades\DB;
use Modules\Beautician\Entities\Beautician;
use Modules\Beautician\Services\BeauticianPortalUserService;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

class PortalProfileUpdateService
{
    /**
     * @param array{first_name: string, last_name: string, email: string, phone: string, date_of_birth?: string|null, job_title?: string|null} $data
     */
    public function update(User $user, Beautician $beautician, array $data): void
    {
        DB::transaction(function () use ($user, $beautician, $data) {
            $phone = PhoneNumber::normalize($data['phone']) ?: $data['phone'];

            $user->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $phone,
                'date_of_birth' => $data['date_of_birth'] ?? null,
            ]);

            $beautician->forceFill([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $phone,
                'job_title' => isset($data['job_title']) ? trim((string) $data['job_title']) ?: null : $beautician->job_title,
            ])->saveQuietly();

            app(BeauticianPortalUserService::class)->ensureBeauticianRole($user);
        });
    }
}
