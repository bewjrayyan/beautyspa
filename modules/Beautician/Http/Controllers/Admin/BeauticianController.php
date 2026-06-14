<?php

namespace Modules\Beautician\Http\Controllers\Admin;

use Modules\Beautician\Entities\Beautician;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Beautician\Http\Requests\SaveBeauticianRequest;
use Modules\SpaBranch\Entities\SpaBranch;
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


    public function edit($id)
    {
        $beautician = $this->getEntity($id);

        if (is_module_enabled('SpaBranch')) {
            $beautician->load('spaBranches');
        }

        $formData = $this->getFormData('edit', $id);

        $data = array_merge([
            'tabs' => TabManager::get($this->getModel()->getTable()),
            $this->getResourceName() => $beautician,
        ], $formData);

        return view("{$this->viewPath}.edit", $data);
    }


    public function store()
    {
        $this->disableSearchSyncing();

        $request = $this->getRequest('store');
        $beautician = $this->makeBeauticianFromRequest($request);
        $beautician->save();
        $this->syncSpaBranches($beautician);

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
            $request->except(array_merge(array_keys(request()->query()), $this->relationRequestKeys()))
        );

        $this->syncSpaBranches($entity);

        $entity->withoutEvents(function () use ($entity) {
            $entity->touch();
        });

        $this->searchable($entity);

        return $this->redirectTo($entity)
            ->withSuccess(trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]));
    }


    protected function createFormData(): array
    {
        return array_merge(
            ['adminUsers' => $this->adminUsersForSelect()],
            $this->spaBranchFormData()
        );
    }


    protected function editFormData($id): array
    {
        return array_merge(
            [
                'adminUsers' => $this->adminUsersForSelect($id),
                'scheduleStats' => is_module_enabled('TreatmentReservation')
                    ? app(ReservationDashboardService::class)->statsForBeauticianSchedule((int) $id)
                    : null,
            ],
            $this->spaBranchFormData($this->getEntity($id))
        );
    }


    protected function redirectTo(Beautician $beautician): RedirectResponse
    {
        return redirect()->route('admin.beauticians.edit', $beautician);
    }


    public function resetPortalPassword(int $id): RedirectResponse
    {
        $request = request();

        $request->validate([
            'portal_password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $beautician = Beautician::findOrFail($id);
        $credentials = app(BeauticianPortalUserService::class)->resetPortalPassword(
            $beautician,
            $request->input('portal_password'),
        );

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
            $request->except(array_merge(array_keys(request()->query()), $this->relationRequestKeys()))
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
    private function relationRequestKeys(): array
    {
        return array_merge(
            $this->portalRequestKeys(),
            is_module_enabled('SpaBranch') ? ['spa_branches', 'spa_branches_present'] : []
        );
    }

    /**
     * @return array<int, string>
     */
    private function portalRequestKeys(): array
    {
        return ['portal_password', 'portal_password_confirmation', 'portal_email'];
    }

    private function syncSpaBranches(Beautician $beautician): void
    {
        if (! is_module_enabled('SpaBranch') || ! request()->has('spa_branches_present')) {
            return;
        }

        $beautician->spaBranches()->sync($this->spaBranchIdsFromRequest());
    }

    /**
     * @return array<int, int>
     */
    private function spaBranchIdsFromRequest(): array
    {
        if (! request()->has('spa_branches_present')) {
            return [];
        }

        return array_values(array_map(
            'intval',
            array_filter((array) request()->input('spa_branches', []))
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function spaBranchFormData(?Beautician $beautician = null): array
    {
        if (! is_module_enabled('SpaBranch')) {
            return [];
        }

        if ($beautician?->exists) {
            $beautician->loadMissing('spaBranches');
        }

        return [
            'spaBranches' => SpaBranch::query()
                ->where('is_active', true)
                ->orderBy('position')
                ->orderBy('name')
                ->pluck('name', 'id'),
            'selectedSpaBranchIds' => $this->selectedSpaBranchIds($beautician),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function selectedSpaBranchIds(?Beautician $beautician = null): array
    {
        if (request()->session()->hasOldInput('spa_branches_present')) {
            return array_values(array_map('intval', (array) old('spa_branches', [])));
        }

        if ($beautician?->exists) {
            return $beautician->spaBranches
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        return [];
    }
}
