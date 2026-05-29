<?php

namespace Modules\User\Http\ViewComposers;

use Exception;
use Illuminate\View\View;
use Modules\User\Support\AuthBranding;

class AuthLayoutComposer
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
            'themeColor' => $this->getThemeColor(),
            'logo' => AuthBranding::logoUrl(preferAdminLogo: true),
            'authLogoHref' => AuthBranding::adminLogoHref(),
            'storefrontHref' => AuthBranding::storefrontHref(),
        ]);
    }


    private function getThemeColor()
    {
        try {
            return tinycolor(storefront_theme_color());
        } catch (Exception $e) {
            return tinycolor('#0068e1');
        }
    }
}
