<?php

namespace Modules\Account\Http\Controllers;

use Modules\Support\Country;
use Illuminate\Routing\Controller;
use Modules\Account\Entities\DefaultAddress;
use Modules\Account\Http\Requests\SaveAddressRequest;
use Modules\Account\Http\Requests\SaveDefaultAddressRequest;

class AccountAddressController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return view('storefront::public.account.addresses.index', [
            'addressesConfig' => [
                'initialAddresses' => $user->addresses->keyBy('id'),
                'initialDefaultAddress' => $user->defaultAddress,
                'countries' => Country::supported(),
                'profileDefaults' => [
                    'first_name' => $user->first_name ?? '',
                    'last_name' => $user->last_name ?? '',
                ],
            ],
        ]);
    }


    public function store(SaveAddressRequest $request)
    {
        $address = $request->user()->addresses()->create($request->validated());

        return response()->json([
            'address' => $address,
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
            'address' => $address,
            'message' => trans('account::messages.address_updated'),
        ]);
    }


    public function destroy($id)
    {
        auth()->user()
            ->addresses()
            ->whereKey($id)
            ->firstOrFail()
            ->delete();

        return response()->json([
            'message' => trans('account::messages.address_deleted'),
        ]);
    }


    public function changeDefault(SaveDefaultAddressRequest $request)
    {
        DefaultAddress::query()->upsert([
            [
                'customer_id' => $request->user()->id,
                'address_id' => $request->validated('address_id'),
            ],
        ], ['customer_id'], ['address_id']);

        return trans('account::messages.default_address_updated');
    }
}
