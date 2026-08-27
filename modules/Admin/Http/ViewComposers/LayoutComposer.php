<?php

namespace Modules\Admin\Http\ViewComposers;

use Illuminate\View\View;
use Modules\Media\Entities\File;
use Illuminate\Support\Facades\Cache;

class LayoutComposer
{

    /**
     * Bind data to the view.
     *
     * @param View $view
     *
     * @return void
     */
    public function compose($view)
    {
        $view->with([
            'logo' => $this->getAdminLogo(),
            'smallLogo' => $this->getAdminSmallLogo(),
            'favicon' => storefront_favicon_url(),
            'faviconMime' => storefront_favicon_mime(),
            'faviconTouch' => storefront_favicon_touch_icon_url(),
        ]);
    }


    private function getMedia($fileId)
    {
        return Cache::rememberForever(md5("files.{$fileId}"), function () use ($fileId) {
            return File::findOrNew($fileId);
        });
    }


    private function getAdminLogo()
    {
        $fileId = setting('admin_sidebar_logo') ?: setting('admin_logo');

        return $this->getMedia($fileId)->path;
    }


    private function getAdminSmallLogo()
    {
        return $this->getMedia(setting('admin_small_logo'))->path;
    }
}
