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
            $limit = min(50, max(1, (int) $request->get('limit', 10)));

            return $this->getModel()
                ->where('name', 'like', '%' . $request->get('query') . '%')
                ->limit($limit)
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


    public function destroy(string $ids)
    {
        $idList = array_values(array_filter(array_map('intval', explode(',', $ids))));

        if ($idList === []) {
            return back();
        }

        $blocked = LoyaltyStampProgram::query()
            ->whereIn('id', $idList)
            ->withCount('wallets')
            ->get()
            ->filter(fn (LoyaltyStampProgram $program) => (int) $program->wallets_count > 0);

        if ($blocked->isNotEmpty()) {
            $names = $blocked->pluck('name')->implode(', ');
            $message = trans('loyalty::stamp_programs.messages.destroy_has_wallets', [
                'programs' => $names,
            ]);

            if (request()->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()
                ->route("{$this->routePrefix}.index")
                ->withError($message);
        }

        $this->getModel()
            ->withoutGlobalScope('active')
            ->whereIn('id', $idList)
            ->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => trans('admin::messages.resource_deleted', ['resource' => $this->getLabel()]),
            ]);
        }

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->withSuccess(trans('admin::messages.resource_deleted', ['resource' => $this->getLabel()]));
    }
}
