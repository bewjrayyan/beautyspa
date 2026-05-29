<?php

namespace Modules\User\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Modules\Account\Services\ProfileAvatarService;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Address\Entities\Address;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Support\Country;
use Modules\User\Http\Requests\UpdateProfileRequest;
use Modules\Loyalty\Services\LoyaltyLifetimeSpendService;
use Modules\Loyalty\Services\LoyaltyTierService;
use Modules\User\Services\ProfileAddressService;

class ProfileController
{
    public function __construct(
        private ProfileAvatarService $avatars,
        private ProfileAddressService $addresses,
        private LoyaltyLifetimeSpendService $lifetimeSpend,
        private LoyaltyTierService $loyaltyTiers
    ) {}


    public function edit()
    {
        $tabs = TabManager::get('profile');
        $profileUser = auth()->user()->load(array_filter([
            'roles',
            'files',
            'defaultAddress.address',
            'addresses',
            app('modules')->isEnabled('Beautician') ? 'beauticianProfile' : null,
        ]));
        $loyaltyWallet = $this->resolveLoyaltyWallet($profileUser);

        if ($loyaltyWallet) {
            $this->lifetimeSpend->recalculateWallet($loyaltyWallet);
            $this->loyaltyTiers->evaluate($loyaltyWallet->fresh(), 'profile_view');
            $loyaltyWallet->refresh();
        }
        $countries = Country::supported();
        $profileAddress = $this->addresses->resolveAddress($profileUser)
            ?? new Address(['country' => setting('default_country', 'MY')]);

        return view('user::admin.profile.edit', compact(
            'tabs',
            'profileUser',
            'loyaltyWallet',
            'countries',
            'profileAddress'
        ));
    }


    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->safe()->except(['avatar', 'remove_avatar', 'password']);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        $this->avatars->syncFromRequest(
            $user,
            $request->file('avatar'),
            $request->boolean('remove_avatar')
        );

        $this->addresses->syncFromRequest($user, $request->only([
            'address_1',
            'address_2',
            'city',
            'state',
            'zip',
            'country',
        ]));

        return back()->withSuccess(trans('admin::messages.resource_updated', [
            'resource' => trans('user::users.profile'),
        ]));
    }


    private function resolveLoyaltyWallet($user): ?LoyaltyWallet
    {
        if (! app('modules')->isEnabled('Loyalty')) {
            return null;
        }

        return LoyaltyWallet::query()
            ->with('tier')
            ->where('user_id', $user->id)
            ->first();
    }
}
