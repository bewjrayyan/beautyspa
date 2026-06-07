<?php

namespace Modules\Setting\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Modules\Media\Entities\File;
use Illuminate\Routing\Redirector;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Artisan;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Support\Services\PWAService;
use Illuminate\Contracts\Foundation\Application;
use Modules\Setting\Http\Requests\UpdateSettingRequest;
use Modules\Setting\Services\AppVersionService;
use Modules\Setting\Services\ArtisanCommandService;

class SettingController
{
    /**
     * Show the form for editing the specified resource.
     *
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function edit()
    {
        $settings = setting()->all();
        $tabs = TabManager::get('settings');

        return view('setting::admin.settings.edit', compact('settings', 'tabs'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSettingRequest $request
     * @param PWAService $PWAService
     *
     * @return Application|\Illuminate\Foundation\Application|RedirectResponse|Redirector
     */
    public function update(UpdateSettingRequest $request, PWAService $PWAService, AppVersionService $appVersion, ArtisanCommandService $artisanCommands)
    {
        if ($request->filled('artisan_action')) {
            return $this->handleArtisanAction($request, $artisanCommands);
        }

        if ($request->filled('app_version_action')) {
            return $this->handleAppVersionAction($request, $appVersion);
        }

        $this->handleMaintenanceMode($request);

        if (setting('pwa_icon') !== request('pwa_icon')) {
            $file = File::find(request('pwa_icon'));
            $file && $PWAService->generateIcons($file);
            $PWAService->updatePWAVersionInServiceWorkerJs();
        }

        setting($request->except('_token', '_method'));

        return redirect(non_localized_url())
            ->with('success', trans('setting::messages.settings_updated'));
    }


    private function handleMaintenanceMode($request)
    {
        if ($request->maintenance_mode) {
            Artisan::call('down');
        } else if (app()->isDownForMaintenance()) {
            Artisan::call('up');
        }
    }


    private function handleArtisanAction(UpdateSettingRequest $request, ArtisanCommandService $artisanCommands): RedirectResponse
    {
        $redirect = redirect()->to(route('admin.settings.edit').'?tab=system');

        try {
            $message = $artisanCommands->run((string) $request->input('artisan_action'));
        } catch (\Throwable $exception) {
            return $redirect->with('error', $exception->getMessage());
        }

        return $redirect->with('success', $message);
    }


    private function handleAppVersionAction(UpdateSettingRequest $request, AppVersionService $appVersion): RedirectResponse
    {
        $redirect = redirect()->to(route('admin.settings.edit').'?tab=system');

        if ($request->input('app_version_action') !== 'pull_latest') {
            return $redirect;
        }

        try {
            $result = $appVersion->pullLatest();
        } catch (\Throwable $exception) {
            return $redirect->with('error', $exception->getMessage());
        }

        $message = $result['updated']
            ? trans('setting::messages.app_version_pulled', ['version' => $result['version']])
            : trans('setting::messages.app_version_already_latest', ['version' => $result['version']]);

        return $redirect->with('success', $message);
    }
}
