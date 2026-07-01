<?php

namespace Modules\User\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Address\Entities\Address;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Account\Services\ProfileAvatarService;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Loyalty\Services\LoyaltyLifetimeSpendService;
use Modules\Loyalty\Services\LoyaltyMemberEnrollmentService;
use Modules\Loyalty\Services\LoyaltyTierService;
use Modules\Loyalty\Services\LoyaltyWalletService;
use Modules\Support\Country;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;
use Modules\User\Http\Requests\SaveUserRequest;
use Modules\User\Services\ProfileAddressService;
use Cartalyst\Sentinel\Laravel\Facades\Activation;

class UserController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'user::users.user';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'user::admin.users';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected $validation = SaveUserRequest::class;


    public function __construct(
        private ProfileAvatarService $avatars,
        private ProfileAddressService $addresses,
        private LoyaltyLifetimeSpendService $lifetimeSpend,
        private LoyaltyTierService $loyaltyTiers,
        private LoyaltyWalletService $loyaltyWallets,
    ) {}


    public function index(Request $request)
    {
        if ($request->has('query')) {
            return $this->getModel()
                ->search($request->get('query'))
                ->query()
                ->limit($request->get('limit', 10))
                ->get();
        }

        $activatedIds = DB::table('activations')
            ->where('completed', true)
            ->distinct()
            ->pluck('user_id');

        return view("{$this->viewPath}.index", [
            'stats' => [
                'total' => User::count(),
                'activated' => User::whereIn('id', $activatedIds)->count(),
                'recent_login' => User::where('last_login', '>=', now()->subDays(30))->count(),
                'new_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            ],
            'role_breakdown' => Role::query()
                ->withCount('users')
                ->having('users_count', '>', 0)
                ->orderByDesc('users_count')
                ->limit(8)
                ->get(),
            'loyalty' => $this->loyaltyIndexStats(),
        ]);
    }


    public function enrollLoyaltyMembers(LoyaltyMemberEnrollmentService $enrollment): RedirectResponse
    {
        if (! app('modules')->isEnabled('Loyalty')) {
            return redirect()
                ->route('admin.users.index')
                ->withError(trans('user::users.index.loyalty_module_disabled'));
        }

        $missing = $enrollment->countMissing(true);

        if ($missing === 0) {
            return redirect()
                ->route('admin.users.index')
                ->withSuccess(trans('user::users.index.loyalty_enroll_none'));
        }

        $result = $enrollment->enrollMissing(true);

        return redirect()
            ->route('admin.users.index')
            ->withSuccess(trans('user::users.index.loyalty_enroll_success', [
                'count' => $result['enrolled'],
            ]));
    }


    public function enrollLoyaltyMembersBulk(
        string $ids,
        LoyaltyMemberEnrollmentService $enrollment
    ): JsonResponse {
        if (! app('modules')->isEnabled('Loyalty')) {
            return response()->json([
                'message' => trans('user::users.index.loyalty_module_disabled'),
            ], 422);
        }

        $userIds = array_filter(array_map('intval', explode(',', $ids)));

        if ($userIds === []) {
            return response()->json([
                'message' => trans('user::users.index.loyalty_enroll_bulk_select_hint'),
            ], 422);
        }

        $result = $enrollment->enrollUserIds($userIds, true);

        if ($result['enrolled'] === 0) {
            return response()->json([
                'message' => trans('user::users.index.loyalty_enroll_bulk_none'),
                'enrolled' => 0,
                'skipped' => $result['skipped'],
            ]);
        }

        return response()->json([
            'message' => trans('user::users.index.loyalty_enroll_bulk_success', [
                'enrolled' => $result['enrolled'],
                'skipped' => $result['skipped'],
            ]),
            'enrolled' => $result['enrolled'],
            'skipped' => $result['skipped'],
        ]);
    }


    /**
     * @return array{enabled: bool, missing: int, members: int}
     */
    private function loyaltyIndexStats(): array
    {
        if (! app('modules')->isEnabled('Loyalty')) {
            return ['enabled' => false, 'missing' => 0, 'members' => 0];
        }

        $enrollment = app(LoyaltyMemberEnrollmentService::class);

        return [
            'enabled' => true,
            'missing' => $enrollment->countMissing(true),
            'members' => LoyaltyWallet::count(),
        ];
    }


    public function create()
    {
        $user = $this->getModel();

        return view("{$this->viewPath}.create", [
            'tabs' => TabManager::get($this->getModel()->getTable()),
            'user' => $user,
            'roles' => Role::list(),
        ]);
    }


    public function edit($id)
    {
        $user = User::query()
            ->with(array_filter([
                'roles',
                'files',
                'defaultAddress.address',
                'addresses',
                app('modules')->isEnabled('Beautician') ? 'beauticianProfile' : null,
            ]))
            ->findOrFail($id);

        $tabs = TabManager::get($this->getModel()->getTable());
        $loyaltyWallet = $this->resolveLoyaltyWallet($user);
        $countries = Country::supported();
        $profileAddress = $this->addresses->resolveAddress($user)
            ?? new Address(['country' => setting('default_country', 'MY')]);

        return view("{$this->viewPath}.edit", [
            'tabs' => $tabs,
            'user' => $user,
            'loyaltyWallet' => $loyaltyWallet,
            'countries' => $countries,
            'profileAddress' => $profileAddress,
            'roles' => Role::list(),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(SaveUserRequest $request)
    {
        $request->merge(['password' => bcrypt($request->password)]);

        $user = User::create($request->except(['permissions', 'avatar', 'remove_avatar']));

        $user->roles()->attach($request->roles);

        $this->syncPermissions($user, $request);

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

        if ($request->input('activated') === '1') {
            Activation::complete($user, Activation::create($user)->code);
        }

        $this->syncBeauticianProfile($user);

        return redirect()->route('admin.users.index')
            ->withSuccess(trans('admin::messages.resource_created', ['resource' => trans('user::users.user')]));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update($id, SaveUserRequest $request)
    {
        $user = User::findOrFail($id);

        $data = $request->safe()->except([
            'avatar',
            'remove_avatar',
            'password',
            'permissions',
            'roles',
            'activated',
        ]);

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

        $user->roles()->sync($request->roles);

        $this->syncPermissions($user, $request);

        if (! Activation::completed($user) && $request->activated === '1') {
            Activation::complete($user, Activation::create($user)->code);
        }

        if (Activation::completed($user) && $request->activated === '0') {
            Activation::remove($user);
        }

        $this->syncBeauticianProfile($user->fresh());

        return back()->withSuccess(trans('admin::messages.resource_updated', [
            'resource' => trans('user::users.user'),
        ]));
    }


    private function resolveLoyaltyWallet(User $user): ?LoyaltyWallet
    {
        if (! app('modules')->isEnabled('Loyalty')) {
            return null;
        }

        if ($user->isCustomer()) {
            $wallet = $this->loyaltyWallets->getOrCreateForUser($user);
        } else {
            $wallet = LoyaltyWallet::query()
                ->with('tier')
                ->where('user_id', $user->id)
                ->first();
        }

        if (! $wallet) {
            return null;
        }

        $this->lifetimeSpend->recalculateWallet($wallet);
        $this->loyaltyTiers->evaluate($wallet->fresh(), 'user_edit');
        $wallet->refresh();

        return $wallet;
    }


    private function syncPermissions(User $user, SaveUserRequest $request): void
    {
        if (! $request->has('permissions')) {
            return;
        }

        $user->permissions = $request->input('permissions', []);
        $user->save();
    }


    private function syncBeauticianProfile(User $user): void
    {
        if (! app('modules')->isEnabled('Beautician')) {
            return;
        }

        app(\Modules\Beautician\Services\UserBeauticianSyncService::class)->syncFromUser($user);
    }
}
