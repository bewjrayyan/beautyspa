<?php

namespace Modules\Account\Http\Controllers;

use Modules\Support\Country;
use Illuminate\Routing\Controller;
use Modules\Address\Entities\DefaultAddress;
use Modules\User\Services\ProfileAddressService;
use Modules\Account\Http\Requests\SaveAddressRequest;
use Modules\Account\Http\Requests\SaveDefaultAddressRequest;

class AccountAddressController extends Controller
{
    public function __construct(
        private ProfileAddressService $profileAddresses,
    ) {}

    public function index()
    {
        $user = auth()->user();
        $this->profileAddresses->ensureFromLastOrder($user);
        $user->load(['addresses', 'defaultAddress.shippingAddress']);

        return view('storefront::public.account.addresses.index', [
            'addressesConfig' => $this->addressesConfig($user),
        ]);
    }


    public function store(SaveAddressRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();
        $validated['first_name'] = filled($validated['first_name'] ?? null)
            ? $validated['first_name']
            : (string) ($user->first_name ?? '');
        $validated['last_name'] = filled($validated['last_name'] ?? null)
            ? $validated['last_name']
            : (string) ($user->last_name ?? '');

        $address = $user->addresses()->create($validated);

        $defaults = DefaultAddress::query()->firstOrCreate(['customer_id' => $user->id]);

        if (! $defaults->address_id) {
            $defaults->update(['address_id' => $address->id]);
        }

        return response()->json([
            'address' => $address->fresh(),
            ...$this->defaultAddressPayload($user->fresh(['defaultAddress'])),
            'message' => trans('account::messages.address_created'),
        ]);
    }


    public function update(SaveAddressRequest $request, $id)
    {
        $address = $request->user()
            ->addresses()
            ->whereKey($id)
            ->firstOrFail();

        $address->update($request->validated());

        return response()->json([
            'address' => $address->fresh(),
            'message' => trans('account::messages.address_updated'),
        ]);
    }


    public function destroy($id)
    {
        $user = auth()->user();
        $defaults = DefaultAddress::query()->where('customer_id', $user->id)->first();

        $address = $user->addresses()
            ->whereKey($id)
            ->firstOrFail();

        $wasBillingDefault = $defaults && (int) $defaults->address_id === (int) $address->id;
        $wasShippingDefault = $defaults && (int) $defaults->shipping_address_id === (int) $address->id;

        $address->delete();

        if ($defaults) {
            if ($wasBillingDefault) {
                $next = $user->addresses()->orderByDesc('id')->first();
                $defaults->address_id = $next?->id;

                if (! $next) {
                    $defaults->shipping_address_id = null;
                }
            }

            if ($wasShippingDefault) {
                $defaults->shipping_address_id = null;
            }

            if ($defaults->address_id) {
                $defaults->save();
            } else {
                $defaults->delete();
            }
        }

        return response()->json([
            'message' => trans('account::messages.address_deleted'),
            ...$this->defaultAddressPayload($user->fresh(['defaultAddress'])),
        ]);
    }


    public function changeDefault(SaveDefaultAddressRequest $request)
    {
        DefaultAddress::query()->updateOrCreate(
            ['customer_id' => $request->user()->id],
            ['address_id' => $request->validated('address_id')]
        );

        return trans('account::messages.default_address_updated');
    }


    public function changeDefaultShipping(SaveDefaultAddressRequest $request)
    {
        DefaultAddress::query()->updateOrCreate(
            ['customer_id' => $request->user()->id],
            ['shipping_address_id' => $request->validated('address_id')]
        );

        return trans('account::messages.default_shipping_address_updated');
    }


    public function useBillingForShipping()
    {
        $defaults = DefaultAddress::query()->where('customer_id', auth()->id())->first();

        if ($defaults) {
            $defaults->update(['shipping_address_id' => null]);
        }

        return trans('account::messages.shipping_same_as_billing_updated');
    }


    private function addressesConfig($user): array
    {
        return [
            'initialAddresses' => $user->addresses->keyBy('id'),
            'initialDefaultAddress' => $user->defaultAddress,
            'shippingSameAsBilling' => $this->profileAddresses->shippingSameAsBilling($user),
            'countries' => Country::supported(),
            'profileDefaults' => [
                'first_name' => $user->first_name ?? '',
                'last_name' => $user->last_name ?? '',
            ],
        ];
    }


    private function defaultAddressPayload($user): array
    {
        $defaults = $user->defaultAddress;

        return [
            'default_address_id' => $defaults?->address_id,
            'shipping_address_id' => $defaults?->shipping_address_id,
            'shipping_same_as_billing' => $this->profileAddresses->shippingSameAsBilling($user),
        ];
    }
}
