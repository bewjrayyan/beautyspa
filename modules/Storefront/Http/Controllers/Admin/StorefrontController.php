<?php

namespace Modules\Storefront\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Storefront\Http\Requests\SaveStorefrontRequest;
use Modules\Support\Services\FaviconService;

class StorefrontController
{
    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit()
    {
        $settings = setting()->all();
        $tabs = TabManager::get('storefront');

        return view('storefront::admin.storefront.edit', compact('settings', 'tabs'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(SaveStorefrontRequest $request, FaviconService $faviconService)
    {
        $previousFavicon = setting('storefront_favicon');

        setting($request->except('_token', '_method'));

        if ((string) $request->input('storefront_favicon', '') !== (string) $previousFavicon) {
            $faviconService->clearCache();
        }

        return back()->withSuccess(trans('admin::messages.resource_updated', ['resource' => trans('setting::settings.settings')]));
    }
}
