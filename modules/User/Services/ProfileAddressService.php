<?php

namespace Modules\User\Services;

use Modules\Address\Entities\Address;
use Modules\Address\Entities\DefaultAddress;
use Modules\Order\Entities\Order;
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


    public function resolveShippingAddress(User $user): ?Address
    {
        $user->loadMissing(['defaultAddress.shippingAddress', 'defaultAddress.address', 'addresses']);

        $shippingId = $user->defaultAddress?->shipping_address_id;
        $billingId = $user->defaultAddress?->address_id;

        if ($shippingId && (int) $shippingId !== (int) $billingId) {
            return $user->defaultAddress->shippingAddress
                ?? $user->addresses->firstWhere('id', $shippingId);
        }

        return $this->resolveAddress($user);
    }


    public function shippingSameAsBilling(User $user): bool
    {
        $user->loadMissing('defaultAddress');

        $shippingId = $user->defaultAddress?->shipping_address_id;
        $billingId = $user->defaultAddress?->address_id;

        return ! $shippingId || (int) $shippingId === (int) $billingId;
    }


    /**
     * Keep saved address recipient names aligned with the profile after a name change.
     */
    public function syncNameFromProfile(User $user): void
    {
        if (! filled($user->first_name) && ! filled($user->last_name)) {
            return;
        }

        $payload = array_filter([
            'first_name' => filled($user->first_name) ? (string) $user->first_name : null,
            'last_name' => filled($user->last_name) ? (string) $user->last_name : null,
        ], fn ($value) => $value !== null);

        if ($payload === []) {
            return;
        }

        $user->addresses()->update($payload);
    }


    /**
     * Seed billing/shipping saved addresses from the customer's latest order when missing.
     */
    public function ensureFromLastOrder(User $user): void
    {
        $this->ensureBillingFromLastOrder($user);
        $this->ensureShippingFromLastOrder($user);
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


    private function ensureBillingFromLastOrder(User $user): ?Address
    {
        if ($this->resolveAddress($user) !== null) {
            return null;
        }

        $lastOrder = $user->orders()->latest()->first();

        if (! $lastOrder instanceof Order || ! filled($lastOrder->billing_address_1)) {
            return null;
        }

        $address = $user->addresses()->create(
            $this->payloadFromOrderBilling($lastOrder, $user)
        );

        $this->ensureDefault($user, $address);

        return $address;
    }


    private function ensureShippingFromLastOrder(User $user): ?Address
    {
        $user->loadMissing('defaultAddress');

        if ($user->defaultAddress?->shipping_address_id) {
            return null;
        }

        $lastOrder = $user->orders()->latest()->first();

        if (! $lastOrder instanceof Order || ! $this->shippingDiffersFromBilling($lastOrder)) {
            return null;
        }

        $address = $this->findOrCreateAddress($user, $this->payloadFromOrderShipping($lastOrder, $user));

        DefaultAddress::query()->updateOrCreate(
            ['customer_id' => $user->id],
            ['shipping_address_id' => $address->id]
        );

        return $address;
    }


    private function shippingDiffersFromBilling(Order $order): bool
    {
        if (! filled($order->shipping_address_1)) {
            return false;
        }

        $pairs = [
            ['billing_address_1', 'shipping_address_1'],
            ['billing_city', 'shipping_city'],
            ['billing_state', 'shipping_state'],
            ['billing_zip', 'shipping_zip'],
            ['billing_country', 'shipping_country'],
        ];

        foreach ($pairs as [$billingKey, $shippingKey]) {
            if (trim((string) $order->{$billingKey}) !== trim((string) $order->{$shippingKey})) {
                return true;
            }
        }

        $billingAddress2 = trim((string) ($order->billing_address_2 ?? ''));
        $shippingAddress2 = trim((string) ($order->shipping_address_2 ?? ''));

        return $billingAddress2 !== $shippingAddress2;
    }


    /**
     * @return array<string, string|null>
     */
    private function payloadFromOrderBilling(Order $order, User $user): array
    {
        return [
            'first_name' => (string) ($user->first_name ?? $order->billing_first_name),
            'last_name' => (string) ($user->last_name ?? $order->billing_last_name),
            'address_1' => (string) $order->billing_address_1,
            'address_2' => filled($order->billing_address_2)
                ? (string) $order->billing_address_2
                : null,
            'city' => (string) $order->billing_city,
            'state' => (string) $order->billing_state,
            'zip' => (string) $order->billing_zip,
            'country' => (string) $order->billing_country,
        ];
    }


    /**
     * @return array<string, string|null>
     */
    private function payloadFromOrderShipping(Order $order, User $user): array
    {
        return [
            'first_name' => (string) ($user->first_name ?? $order->shipping_first_name),
            'last_name' => (string) ($user->last_name ?? $order->shipping_last_name),
            'address_1' => (string) $order->shipping_address_1,
            'address_2' => filled($order->shipping_address_2)
                ? (string) $order->shipping_address_2
                : null,
            'city' => (string) $order->shipping_city,
            'state' => (string) $order->shipping_state,
            'zip' => (string) $order->shipping_zip,
            'country' => (string) $order->shipping_country,
        ];
    }


    /**
     * @param  array<string, string|null>  $payload
     */
    private function findOrCreateAddress(User $user, array $payload): Address
    {
        $existing = $user->addresses()
            ->where('address_1', $payload['address_1'])
            ->where('city', $payload['city'])
            ->where('zip', $payload['zip'])
            ->where('country', $payload['country'])
            ->first();

        if ($existing instanceof Address) {
            return $existing;
        }

        return $user->addresses()->create($payload);
    }


    private function ensureDefault(User $user, Address $address): void
    {
        DefaultAddress::updateOrCreate(
            ['customer_id' => $user->id],
            ['address_id' => $address->id]
        );
    }
}
