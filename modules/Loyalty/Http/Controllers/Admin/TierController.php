<?php

namespace Modules\Loyalty\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Loyalty\Entities\LoyaltyTier;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Loyalty\Http\Requests\SaveTierRequest;

class TierController
{
    use HasCrudActions;

    protected $model = LoyaltyTier::class;

    protected $label = 'loyalty::tiers.tier';

    protected $viewPath = 'loyalty::admin.tiers';

    protected $validation = SaveTierRequest::class;

    protected $routePrefix = 'admin.loyalty.tiers';


    public function index(Request $request)
    {
        if ($request->has('query')) {
            return $this->getModel()
                ->search($request->get('query'))
                ->query()
                ->limit($request->get('limit', 10))
                ->get();
        }

        $tiers = LoyaltyTier::query()
            ->orderBy('sort_order')
            ->withCount('wallets')
            ->get();

        return view("{$this->viewPath}.index", [
            'tiers' => $tiers,
            'stats' => [
                'total' => $tiers->count(),
                'active' => $tiers->where('is_active', true)->count(),
                'total_members' => LoyaltyWallet::count(),
            ],
        ]);
    }


    public function create()
    {
        $tier = new LoyaltyTier([
            'is_active' => true,
            'earn_multiplier' => 1,
            'min_lifetime_spend' => 0,
            'sort_order' => 0,
        ]);

        return view("{$this->viewPath}.create", [
            'tier' => $tier,
            'currencySymbol' => currency_symbol(setting('default_currency')),
        ]);
    }


    public function edit($id)
    {
        $tier = $this->getEntity($id);
        $tier->loadCount('wallets');

        return view("{$this->viewPath}.edit", [
            'tier' => $tier,
            'currencySymbol' => currency_symbol(setting('default_currency')),
        ]);
    }
}
