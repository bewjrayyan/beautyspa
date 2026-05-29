<?php

namespace Modules\Beautician\Observers;

use Modules\Beautician\Entities\Beautician;
use Modules\Beautician\Services\BeauticianPortalUserService;

class BeauticianObserver
{
    public function saved(Beautician $beautician): void
    {
        $credentials = app(BeauticianPortalUserService::class)->sync($beautician);

        $beautician->portalPassword = null;
        $beautician->portalEmail = null;

        if ($credentials) {
            session()->flash('beautician_portal_credentials', $credentials);
        }
    }
}
