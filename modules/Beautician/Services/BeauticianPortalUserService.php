<?php

namespace Modules\Beautician\Services;

use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Illuminate\Support\Str;
use Modules\Beautician\Entities\Beautician;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

class BeauticianPortalUserService
{
    /**
     * @return array{email: string, password?: string}|null
     */
    public function sync(Beautician $beautician): ?array
    {
        if (! $beautician->is_active) {
            return null;
        }

        $password = $this->normalizePassword($beautician->portalPassword);

        if ($beautician->user_id) {
            $user = $beautician->user;

            if ($user) {
                $this->ensureBeauticianRole($user);
                $this->syncUserFromBeautician($user, $beautician, $password);
            }

            return $password
                ? ['email' => $user?->email ?? '', 'password_updated' => true]
                : null;
        }

        $user = $this->createPortalUser(
            $beautician,
            $password,
            $this->normalizeEmail($beautician->portalEmail)
        );

        $beautician->forceFill(['user_id' => $user->id])->saveQuietly();

        if ($password) {
            return ['email' => $user->email, 'password_set' => true];
        }

        return [
            'email' => $user->email,
            'password' => $user->plainPassword,
            'created' => true,
        ];
    }


    public function ensureBeauticianRole(User $user): void
    {
        $role = $this->beauticianRole();

        if (! $role || $user->roles()->where('roles.id', $role->id)->exists()) {
            return;
        }

        $user->roles()->attach($role);
    }


    /**
     * @return array{email: string, password?: string, password_updated?: bool}|null
     */
    public function resetPortalPassword(Beautician $beautician, ?string $password = null): ?array
    {
        if (! $beautician->user_id || ! $beautician->user) {
            return null;
        }

        $providedPassword = $this->normalizePassword($password);
        $password = $providedPassword ?: Str::password(12);

        $beautician->user->update([
            'password' => bcrypt($password),
        ]);

        $this->ensureBeauticianRole($beautician->user);

        if ($providedPassword) {
            return [
                'email' => $beautician->user->email,
                'password_updated' => true,
            ];
        }

        return [
            'email' => $beautician->user->email,
            'password' => $password,
        ];
    }


    private function createPortalUser(
        Beautician $beautician,
        ?string $password = null,
        ?string $email = null
    ): User {
        $plainPassword = $password ?: Str::password(12);

        $user = User::create([
            'first_name' => $beautician->first_name,
            'last_name' => $beautician->last_name,
            'email' => $email ?: $this->portalEmailFor($beautician),
            'phone' => PhoneNumber::normalize($beautician->phone) ?: $beautician->phone,
            'password' => bcrypt($plainPassword),
        ]);

        if (! $password) {
            $user->plainPassword = $plainPassword;
        }

        $this->ensureBeauticianRole($user);

        Activation::complete($user, Activation::create($user)->code);

        return $user;
    }


    private function syncUserFromBeautician(User $user, Beautician $beautician, ?string $password = null): void
    {
        $updates = [
            'first_name' => $beautician->first_name,
            'last_name' => $beautician->last_name,
            'phone' => PhoneNumber::normalize($beautician->phone) ?: $beautician->phone,
        ];

        if ($password) {
            $updates['password'] = bcrypt($password);
        }

        $user->update($updates);
    }


    private function portalEmailFor(Beautician $beautician): string
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $phoneDigits = preg_replace('/\D/', '', (string) $beautician->phone) ?: 'user';
        $email = "{$phoneDigits}@beautician.{$host}";

        if (! User::where('email', $email)->exists()) {
            return $email;
        }

        return "beautician.{$beautician->id}@{$host}";
    }


    private function beauticianRole(): ?Role
    {
        return Role::whereTranslation('name', 'Beautician')->first();
    }


    private function normalizePassword(?string $password): ?string
    {
        $password = is_string($password) ? trim($password) : '';

        return $password !== '' ? $password : null;
    }


    private function normalizeEmail(?string $email): ?string
    {
        $email = is_string($email) ? trim($email) : '';

        return $email !== '' ? $email : null;
    }
}
