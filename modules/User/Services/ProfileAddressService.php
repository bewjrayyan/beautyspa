<?php

namespace Modules\User\Services;

use Modules\Address\Entities\Address;
use Modules\Address\Entities\DefaultAddress;
use Modules\User\Entities\User;

class ProfileAddressService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function syncFromRequest(User $user, array $data): void
    {
        $payload = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'address_1' => $data['address_1'] ?? null,
            'address_2' => $data['address_2'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'zip' => $data['zip'] ?? null,
            'country' => $data['country'] ?? null,
        ];

        if (! $this->hasAddressData($payload)) {
            return;
        }

        $existing = $this->resolveAddress($user);

        if ($existing) {
            $existing->update($payload);

            $this->ensureDefault($user, $existing);

            return;
        }

        $address = $user->addresses()->create($payload);

        $this->ensureDefault($user, $address);
    }


    public function resolveAddress(User $user): ?Address
    {
        $user->loadMissing(['defaultAddress.address', 'addresses']);

        if ($user->defaultAddress?->address_id) {
            return $user->defaultAddress->address;
        }

        return $user->addresses->first();
    }


    /**
     * @param  array<string, mixed>  $payload
     */
    private function hasAddressData(array $payload): bool
    {
        return filled($payload['address_1'])
            || filled($payload['city'])
            || filled($payload['state'])
            || filled($payload['zip'])
            || filled($payload['country']);
    }


    private function ensureDefault(User $user, Address $address): void
    {
        DefaultAddress::updateOrCreate(
            ['customer_id' => $user->id],
            ['address_id' => $address->id]
        );
    }
}
