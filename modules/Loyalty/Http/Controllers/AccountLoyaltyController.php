<?php

namespace Modules\Loyalty\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Loyalty\Entities\LoyaltyStampWallet;
use Modules\Loyalty\Services\LoyaltyConfig;
use Modules\Loyalty\Services\LoyaltyStampProgressService;
use Modules\Loyalty\Services\LoyaltyStampRedeemService;
use Modules\Loyalty\Services\LoyaltyWalletService;
use Modules\Loyalty\Services\LoyaltyReferralService;

class AccountLoyaltyController
{
    public function __construct(
        private LoyaltyWalletService $wallets,
        private LoyaltyConfig $config,
        private LoyaltyReferralService $referrals,
        private LoyaltyStampProgressService $stampProgress,
        private LoyaltyStampRedeemService $stampRedeem
    ) {}


    public function index(Request $request)
    {
        $wallet = $this->wallets->getOrCreateForUser($request->user());
        $wallet->load('tier');

        $transactions = $wallet->transactions()->limit(50)->get();

        $user = $request->user();
        $referralCode = $this->referrals->ensureReferralCode($user);

        return view('loyalty::public.account.loyalty.index', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'pointValueRm' => $this->config->pointValueRm(),
            'balanceRm' => $this->config->pointsToRm($wallet->balance),
            'referralCode' => $referralCode,
            'referralEnabled' => $this->config->referralEnabled(),
            'stampCards' => $this->stampProgress->forAccount($user),
            'stampRedemptions' => $this->stampProgress->recentRedemptions($user),
        ]);
    }


    public function redeemStamp(Request $request, LoyaltyStampWallet $wallet): RedirectResponse
    {
        try {
            $code = $this->stampRedeem->redeem($wallet, $request->user());

            return redirect()
                ->route('account.loyalty.index')
                ->with('stamp_redeemed_code', $code)
                ->with('success', trans('loyalty::account.stamp_redeemed', ['code' => $code]));
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('account.loyalty.index')
                ->with('error', $e->getMessage());
        }
    }
}
