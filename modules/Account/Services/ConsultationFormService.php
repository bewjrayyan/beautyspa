<?php

namespace Modules\Account\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\User\Entities\User;

class ConsultationFormService
{
    public function pendingFor(User $user): Collection
    {
        return ConsultationSubmission::query()
            ->forConsultationList()
            ->where('user_id', $user->id)
            ->whereNull('submitted_at')
            ->whereNull('revoked_at')
            ->latest('sent_at')
            ->limit(50)
            ->get()
            ->tap(fn (Collection $forms) => $this->loadLegacyContext($forms));
    }

    public function historyFor(User $user): LengthAwarePaginator
    {
        $submissions = ConsultationSubmission::query()
            ->forConsultationList()
            ->where('user_id', $user->id)
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->paginate(15, ['*'], 'consultations_page')
            ->withQueryString();

        $this->loadLegacyContext($submissions->getCollection());

        return $submissions;
    }

    public function recentRequests(int $limit = 20): Collection
    {
        $submissions = ConsultationSubmission::query()
            ->forConsultationList()
            ->addSelect(['customer_name', 'customer_email', 'template_version'])
            ->latest('sent_at')
            ->limit(max(1, min($limit, 100)))
            ->get();

        $this->loadLegacyContext($submissions);

        return $submissions;
    }

    private function loadLegacyContext(Collection $submissions): void
    {
        $legacy = $submissions
            ->filter(fn (ConsultationSubmission $submission): bool => ! is_array($submission->context_snapshot));

        if ($legacy->isEmpty()) {
            return;
        }

        $legacy->load([
            'treatmentBooking.product',
            'treatmentBooking.beautician.spaBranches',
            'beautician',
            'product',
            'order.spaBranch',
            'order.beautician',
            'orderProduct',
        ]);
    }
}
