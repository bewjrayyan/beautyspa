<?php

namespace Modules\Account\Services;

use Illuminate\Support\Collection;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\User\Entities\User;

class ConsultationFormService
{
    public function pendingFor(User $user): Collection
    {
        return ConsultationSubmission::query()
            ->withConsultationContext()
            ->where('user_id', $user->id)
            ->whereNull('submitted_at')
            ->whereNull('revoked_at')
            ->latest('sent_at')
            ->get();
    }

    public function historyFor(User $user): Collection
    {
        return ConsultationSubmission::query()
            ->withConsultationContext()
            ->where('user_id', $user->id)
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->get();
    }
}
