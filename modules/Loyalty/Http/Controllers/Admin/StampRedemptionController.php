<?php

namespace Modules\Loyalty\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Loyalty\Entities\LoyaltyStampWallet;
use Modules\Loyalty\Services\LoyaltyStampAdminService;

class StampRedemptionController
{
    public function __construct(private LoyaltyStampAdminService $stamps) {}


    public function lookup(Request $request)
    {
        $code = (string) $request->input('code', '');

        if ($request->wantsJson()) {
            return response()->json($this->stamps->lookupByCode($code));
        }

        return redirect()->route('admin.loyalty.members.index', array_filter([
            'code' => $code !== '' ? strtoupper(trim($code)) : null,
        ]));
    }


    public function fulfill(LoyaltyStampWallet $wallet): RedirectResponse
    {
        try {
            $this->stamps->markFulfilled($wallet, (int) auth()->id());

            return back()->withSuccess(trans('loyalty::members.stamps.fulfill_success'));
        } catch (\InvalidArgumentException $e) {
            return back()->withError($e->getMessage());
        }
    }
}
