<?php

namespace Modules\User\Support;

use Modules\User\Entities\User;
use Modules\User\Events\CustomerRegistered;

class DeferCustomerRegistered
{
    /**
     * Run wallet, referral, and welcome notifications after the HTTP response is sent.
     */
    public static function dispatch(User $user, ?string $referralCode = null): void
    {
        $userId = $user->id;
        $referralCode ??= request()->input('referral_code');

        dispatch(function () use ($userId, $referralCode): void {
            $user = User::query()->find($userId);

            if ($user === null) {
                return;
            }

            event(new CustomerRegistered($user, $referralCode));
        })->afterResponse();
    }
}
