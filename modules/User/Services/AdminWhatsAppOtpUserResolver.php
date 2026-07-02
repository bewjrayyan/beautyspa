<?php

namespace Modules\User\Services;

use Exception;
use Modules\Beautician\Entities\Beautician;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

class AdminWhatsAppOtpUserResolver
{
    /**
     * @throws Exception
     */
    public function resolve(string $phone): User
    {
        $normalized = PhoneNumber::normalize($phone);

        if ($normalized === '') {
            throw new Exception(trans('user::messages.whatsapp_otp.invalid_phone'));
        }

        $beauticianUser = $this->resolveBeauticianUser($normalized);

        if ($beauticianUser) {
            return $beauticianUser;
        }

        $staffUser = $this->resolveStaffUser($normalized);

        if ($staffUser) {
            return $staffUser;
        }

        throw new Exception(trans('user::messages.whatsapp_otp.admin_no_account'));
    }


    private function resolveBeauticianUser(string $normalized): ?User
    {
        $beautician = Beautician::query()
            ->where('is_active', true)
            ->whereNotNull('user_id')
            ->whereIn('phone', PhoneNumber::variants($normalized))
            ->with('user')
            ->first();

        return $beautician?->user;
    }


    private function resolveStaffUser(string $normalized): ?User
    {
        $user = User::findByPhone($normalized);

        if (! $user || $user->isCustomer()) {
            return null;
        }

        return $user;
    }
}
