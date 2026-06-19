<?php

namespace Modules\Loyalty\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Loyalty\Entities\LoyaltyStampProgram;
use Modules\Loyalty\Entities\LoyaltyStampWallet;
use Modules\Loyalty\Http\Requests\SaveStampProgramRequest;
use Modules\Loyalty\Services\StampProgramEligibleProductService;

class StampProgramController
{
    use HasCrudActions;

    protected $model = LoyaltyStampProgram::class;

    protected $label = 'loyalty::stamp_programs.program';

    protected $viewPath = 'loyalty::admin.stamp_programs';

    protected $validation = SaveStampProgramRequest::class;

    protected $routePrefix = 'admin.loyalty.stamp_programs';


    public function index(Request $request)
    {
        if ($request->has('query')) {
            return $this->getModel()
                ->where('name', 'like', '%' . $request->get('query') . '%')
                ->limit($request->get('limit', 10))
                ->get();
        }

        $programs = LoyaltyStampProgram::query()
            ->orderBy('sort_order')
            ->withCount('wallets')
            ->get();

        return view("{$this->viewPath}.index", [
            'programs' => $programs,
            'stats' => [
                'total' => $programs->count(),
                'active' => $programs->where('is_active', true)->count(),
                'active_cards' => LoyaltyStampWallet::query()
                    ->whereNull('completed_at')
                    ->whereNull('redeemed_at')
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->count(),
            ],
        ]);
    }


    public function create()
    {
        $program = new LoyaltyStampProgram([
            'is_active' => true,
            'stamps_required' => 7,
            'validity_days' => 30,
            'virtual_treatments_only' => true,
            'sort_order' => 0,
        ]);

        $eligible = app(StampProgramEligibleProductService::class);

        return view("{$this->viewPath}.create", [
            'program' => $program,
            'categories' => $eligible->categoryOptions(),
            'eligibleSelection' => ['category_ids' => [], 'products' => []],
        ]);
    }


    public function edit($id)
    {
        $program = $this->getEntity($id);
        $program->loadCount('wallets');

        $eligible = app(StampProgramEligibleProductService::class);

        return view("{$this->viewPath}.edit", [
            'program' => $program,
            'categories' => $eligible->categoryOptions(),
            'eligibleSelection' => $eligible->serializeForAdmin($program),
        ]);
    }
}
