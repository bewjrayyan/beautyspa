<?php

namespace Modules\Loyalty\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Loyalty\Services\LoyaltyConfig;
use Modules\Loyalty\Services\LoyaltyWalletService;
use Modules\Loyalty\Services\LoyaltyReferralService;

class AccountLoyaltyController
{
    public function __construct(
        private LoyaltyWalletService $wallets,
        private LoyaltyConfig $config,
        private LoyaltyReferralService $referrals
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
        ]);
    }
}
