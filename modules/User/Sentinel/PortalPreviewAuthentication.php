<?php

namespace Modules\User\Sentinel;

use Modules\TreatmentReservation\Services\AdminPortalPreview;
use Modules\User\Contracts\Authentication;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;
use Cartalyst\Sentinel\Activations\ActivationInterface;

class PortalPreviewAuthentication implements Authentication
{
    public function __construct(
        private SentinelAuthentication $auth,
        private AdminPortalPreview $preview,
    ) {}


    public function login($credentials, $remember = false)
    {
        return $this->auth->login($credentials, $remember);
    }


    public function register($data)
    {
        return $this->auth->register($data);
    }


    public function registerAndActivate($data)
    {
        return $this->auth->registerAndActivate($data);
    }


    public function activate($userId, $code)
    {
        return $this->auth->activate($userId, $code);
    }


    public function assignRole(User $user, Role $role)
    {
        return $this->auth->assignRole($user, $role);
    }


    public function logout()
    {
        return $this->auth->logout();
    }


    public function createActivation(User $user)
    {
        return $this->auth->createActivation($user);
    }


    public function createReminderCode(User $user)
    {
        return $this->auth->createReminderCode($user);
    }


    public function completeResetPassword(User $user, $code, $password)
    {
        return $this->auth->completeResetPassword($user, $code, $password);
    }


    public function hasAccess($permissions)
    {
        $user = $this->preview->effectiveUser();

        if ($user) {
            $permissions = is_array($permissions) ? $permissions : func_get_args();

            return $user->hasAccess($permissions);
        }

        return $this->auth->hasAccess($permissions);
    }


    public function hasAnyAccess($permissions)
    {
        $user = $this->preview->effectiveUser();

        if ($user) {
            $permissions = is_array($permissions) ? $permissions : func_get_args();

            return $user->hasAnyAccess($permissions);
        }

        return $this->auth->hasAnyAccess($permissions);
    }


    public function check()
    {
        return $this->auth->check();
    }


    public function user()
    {
        return $this->preview->effectiveUser() ?? $this->auth->user();
    }


    public function id()
    {
        return optional($this->user())->id;
    }
}
