<?php

namespace Modules\Beautician\Http\Controllers\Admin;

use Modules\Beautician\Entities\Beautician;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Beautician\Http\Requests\SaveBeauticianRequest;
use Modules\Beautician\Services\BeauticianPortalUserService;
use Modules\TreatmentReservation\Services\ReservationDashboardService;
use Modules\User\Entities\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BeauticianController
{
    use HasCrudActions;

    protected $model = Beautician::class;

    protected $label = 'beautician::beauticians.beautician';

    protected $viewPath = 'beautician::admin.beauticians';

    protected $validation = SaveBeauticianRequest::class;


    public function store()
    {
        $this->disableSearchSyncing();

        $request = $this->getRequest('store');
        $beautician = $this->makeBeauticianFromRequest($request);
        $beautician->save();

        $this->searchable($beautician);

        return $this->redirectTo($beautician)
            ->withSuccess(trans('admin::messages.resource_created', ['resource' => $this->getLabel()]));
    }


    public function update($id)
    {
        $entity = $this->getEntity($id);

        $this->disableSearchSyncing();

        $request = $this->getRequest('update');
        $this->applyPortalInput($entity, $request);

        $entity->update(
            $request->except(array_merge(array_keys(request()->query()), $this->portalRequestKeys()))
        );

        $entity->withoutEvents(function () use ($entity) {
            $entity->touch();
        });

        $this->searchable($entity);

        return $this->redirectTo($entity)
            ->withSuccess(trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]));
    }


    protected function createFormData(): array
    {
        return ['adminUsers' => $this->adminUsersForSelect()];
    }


    protected function editFormData($id): array
    {
        return [
            'adminUsers' => $this->adminUsersForSelect($id),
            'scheduleStats' => is_module_enabled('TreatmentReservation')
                ? app(ReservationDashboardService::class)->statsForBeauticianSchedule((int) $id)
                : null,
        ];
    }


    protected function redirectTo(Beautician $beautician): RedirectResponse
    {
        return redirect()->route('admin.beauticians.edit', $beautician);
    }


    public function resetPortalPassword(int $id): RedirectResponse
    {
        $beautician = Beautician::findOrFail($id);
        $credentials = app(BeauticianPortalUserService::class)->resetPortalPassword($beautician);

        if (! $credentials) {
            return back()->withError(trans('beautician::beauticians.form.portal_reset_no_account'));
        }

        return back()->with('beautician_portal_credentials', $credentials);
    }


    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function adminUsersForSelect(?int $currentBeauticianId = null): array
    {
        $assignedIds = Beautician::query()
            ->when($currentBeauticianId, fn ($q) => $q->where('id', '!=', $currentBeauticianId))
            ->whereNotNull('user_id')
            ->pluck('user_id');

        return User::query()
            ->whereNotIn('id', $assignedIds)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'label' => trim("{$user->first_name} {$user->last_name}") . " ({$user->email})",
            ])
            ->values()
            ->all();
    }


    private function makeBeauticianFromRequest(Request $request): Beautician
    {
        $beautician = $this->getModel()->make(
            $request->except(array_merge(array_keys(request()->query()), $this->portalRequestKeys()))
        );

        $this->applyPortalInput($beautician, $request);

        return $beautician;
    }


    private function applyPortalInput(Beautician $beautician, Request $request): void
    {
        $beautician->portalPassword = $request->input('portal_password') ?: null;
        $beautician->portalEmail = $request->filled('user_id')
            ? null
            : ($request->input('portal_email') ?: null);
    }


    /**
     * @return array<int, string>
     */
    private function portalRequestKeys(): array
    {
        return ['portal_password', 'portal_password_confirmation', 'portal_email'];
    }
}
