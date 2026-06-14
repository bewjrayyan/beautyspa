<?php

namespace Modules\User\Http\ViewComposers;

use Illuminate\View\View;

class CurrentUserComposer
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
        $user = auth()->user();

        if ($user && request()->is('*admin*')) {
            $user = effective_admin_user() ?? $user;
        }

        $view->with('currentUser', $user);
    }
}
