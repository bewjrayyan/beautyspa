<?php

namespace Modules\Beautician\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Beautician\Entities\Beautician;
use Modules\Beautician\Http\Requests\RegisterBeauticianRequest;
use Modules\Beautician\Services\BeauticianProfilePhotoService;
use Modules\Beautician\Support\JobTitleOptions;
use Modules\Page\Entities\Page;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\User\Contracts\Authentication;
use Modules\User\Entities\Role;

class BeauticianRegistrationController extends Controller
{
    public function __construct(
        private readonly Authentication $auth,
        private readonly BeauticianProfilePhotoService $profilePhotos,
    ) {
        $this->middleware('guest')->except('pending');
    }


    public function create(): View
    {
        return view('beautician::public.registration.create', [
            'jobTitles' => JobTitleOptions::activeNames(),
            'spaBranches' => $this->spaBranches(),
            'privacyPageUrl' => $this->privacyPageUrl(),
        ]);
    }


    public function store(RegisterBeauticianRequest $request): RedirectResponse
    {
        $role = Role::whereTranslation('name', 'Beautician')->first();

        if (! $role) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'profile_image']))
                ->withError(trans('beautician::beauticians.self_registration.role_unavailable'));
        }

        $beautician = DB::transaction(function () use ($request, $role) {
            $user = $this->auth->registerAndActivate($request->only([
                'first_name',
                'last_name',
                'email',
                'phone',
                'password',
            ]));

            $user->roles()->syncWithoutDetaching([$role->id]);
            $user->flushRoleCache();

            $beautician = Beautician::create([
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'profile_color' => $this->profileColor($user->id),
                'job_title' => $request->input('job_title'),
                'is_active' => false,
                'position' => ((int) Beautician::query()->max('position')) + 1,
            ]);

            if (is_module_enabled('SpaBranch')) {
                $beautician->spaBranches()->sync(
                    array_map('intval', (array) $request->input('spa_branches', []))
                );
            }

            if ($request->hasFile('profile_image')) {
                $this->profilePhotos->attachUpload(
                    $beautician,
                    $request->file('profile_image'),
                    (int) $user->id,
                );
            }

            return $beautician;
        });

        $this->auth->login([
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ], true);

        return redirect()
            ->route('admin.beauticians.portal.dashboard', $beautician->id)
            ->withSuccess(trans('beautician::beauticians.self_registration.submitted_portal'));
    }


    public function pending(): View
    {
        return view('beautician::public.registration.pending');
    }


    private function spaBranches()
    {
        if (! is_module_enabled('SpaBranch')) {
            return collect();
        }

        return SpaBranch::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('name')
            ->pluck('name', 'id');
    }


    private function privacyPageUrl(): string
    {
        $url = Cache::tags('settings')->rememberForever(
            'privacy_page_url:'.locale(),
            fn () => Page::urlForPage(setting('storefront_privacy_page'))
        );

        $normalized = storefront_content_url($url);

        if (! $normalized || $normalized === '#') {
            return '#';
        }

        $path = parse_url($normalized, PHP_URL_PATH) ?: '/';
        $appUrl = parse_url((string) config('app.url'));

        if (! is_array($appUrl) || empty($appUrl['scheme']) || empty($appUrl['host'])) {
            return '#';
        }

        $origin = $appUrl['scheme'].'://'.$appUrl['host'];

        if (isset($appUrl['port'])) {
            $origin .= ':'.$appUrl['port'];
        }

        return $origin.'/'.ltrim($path, '/');
    }


    private function profileColor(int $userId): string
    {
        $colors = ['#6366f1', '#ec4899', '#f274ac', '#047857', '#ea580c', '#4338ca'];

        return $colors[$userId % count($colors)];
    }
}
