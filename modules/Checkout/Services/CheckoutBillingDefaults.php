<?php

namespace Modules\Checkout\Services;

use Modules\Order\Entities\Order;
use Modules\Support\Country;
use Modules\User\Entities\User;
use Modules\User\Services\ProfileAddressService;

class CheckoutBillingDefaults
{
    /**
     * @return array<string, string>|null
     */
    public function forUser(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        $address = app(ProfileAddressService::class)->resolveAddress($user);

        if ($address !== null) {
            return $this->fromAddress($address);
        }

        $lastOrder = $user->orders()->latest()->first();

        if ($lastOrder instanceof Order && filled($lastOrder->billing_address_1)) {
            return $this->fromOrder($lastOrder);
        }

        return $this->fromProfile($user);
    }

    /**
     * @return array<string, string>
     */
    private function fromProfile(User $user): array
    {
        $supported = Country::supported();
        $country = setting('store_country');

        if (! is_string($country) || $country === '' || ! array_key_exists($country, $supported)) {
            $country = (string) array_key_first($supported);
        }

        return [
            'first_name' => (string) ($user->first_name ?? ''),
            'last_name' => (string) ($user->last_name ?? ''),
            'address_1' => '',
            'address_2' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'country' => $country,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function fromOrder(Order $order): array
    {
        return [
            'first_name' => (string) $order->billing_first_name,
            'last_name' => (string) $order->billing_last_name,
            'address_1' => (string) $order->billing_address_1,
            'address_2' => (string) ($order->billing_address_2 ?? ''),
            'city' => (string) $order->billing_city,
            'state' => (string) $order->billing_state,
            'zip' => (string) $order->billing_zip,
            'country' => (string) $order->billing_country,
        ];
    }

    /**
     * @param  \Modules\Address\Entities\Address  $address
     * @return array<string, string>
     */
    private function fromAddress($address): array
    {
        return [
            'first_name' => (string) $address->first_name,
            'last_name' => (string) $address->last_name,
            'address_1' => (string) $address->address_1,
            'address_2' => (string) ($address->address_2 ?? ''),
            'city' => (string) $address->city,
            'state' => (string) $address->state,
            'zip' => (string) $address->zip,
            'country' => (string) $address->country,
        ];
    }
}
