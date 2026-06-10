<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Account\Services\ProfileAvatarService;
use Modules\Loyalty\Services\LoyaltyConfig;
use Modules\Loyalty\Services\LoyaltyWalletService;
use Modules\User\Http\Requests\UpdateProfileRequest;

class AccountProfileController
{
    public function __construct(
        private ProfileAvatarService $avatars
    ) {}


    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit()
    {
        $account = auth()->user();
        $account->load('files');

        $loyaltyWallet = null;
        $loyaltyBalanceRm = 0;
        $loyaltyEarnRate = 0;

        if (app('modules')->isEnabled('Loyalty')) {
            $loyaltyWallet = app(LoyaltyWalletService::class)->getOrCreateForUser($account);
            $loyaltyWallet->load('tier');

            $loyaltyConfig = app(LoyaltyConfig::class);
            $loyaltyBalanceRm = $loyaltyConfig->pointsToRm($loyaltyWallet->balance);
            $loyaltyEarnRate = $loyaltyConfig->earnRatePerRm();
        }

        return view('storefront::public.account.profile.edit', [
            'account' => $account,
            'loyaltyWallet' => $loyaltyWallet,
            'loyaltyBalanceRm' => $loyaltyBalanceRm,
            'loyaltyEarnRate' => $loyaltyEarnRate,
        ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProfileRequest $request
     *
     * @return Response
     */
    public function update(UpdateProfileRequest $request)
    {
        $request->bcryptPassword();

        $user = auth()->user();

        $data = $request->safe()->except(['avatar', 'remove_avatar', 'password']);

        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }

        $user->update($data);

        $this->avatars->syncFromRequest(
            $user,
            $request->file('avatar'),
            $request->boolean('remove_avatar')
        );

        return back()->with('success', trans('account::messages.profile_updated'));
    }
}
