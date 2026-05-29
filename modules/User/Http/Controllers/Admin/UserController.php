<?php

namespace Modules\User\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Address\Entities\Address;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Account\Services\ProfileAvatarService;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Loyalty\Services\LoyaltyLifetimeSpendService;
use Modules\Loyalty\Services\LoyaltyTierService;
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
        private LoyaltyTierService $loyaltyTiers
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

        $user = User::create($request->except(['permissions']));

        $user->roles()->attach($request->roles);

        $this->syncPermissions($user, $request);

        Activation::complete($user, Activation::create($user)->code);

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

        $wallet = LoyaltyWallet::query()
            ->with('tier')
            ->where('user_id', $user->id)
            ->first();

        if ($wallet) {
            $this->lifetimeSpend->recalculateWallet($wallet);
            $this->loyaltyTiers->evaluate($wallet->fresh(), 'user_edit');
            $wallet->refresh();
        }

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
