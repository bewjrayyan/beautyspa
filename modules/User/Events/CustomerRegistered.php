<?php

namespace Modules\User\Events;

use Modules\User\Entities\User;
use Illuminate\Queue\SerializesModels;

class CustomerRegistered
{
    use SerializesModels;

    /**
     * The instance of user.
     *
     * @var User
     */
    public $user;

    /**
     * Referral code submitted with registration (when deferred off the HTTP request).
     */
    public ?string $referralCode;


    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param string|null $referralCode
     *
     * @return void
     */
    public function __construct(User $user, ?string $referralCode = null)
    {
        $this->user = $user;
        $this->referralCode = $referralCode;
    }
}
