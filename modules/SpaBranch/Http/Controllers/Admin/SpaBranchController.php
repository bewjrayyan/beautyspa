<?php

namespace Modules\SpaBranch\Http\Controllers\Admin;

use Modules\Admin\Traits\HasCrudActions;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Beautician\Entities\Beautician;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\SpaBranch\Http\Requests\SaveSpaBranchRequest;

class SpaBranchController
{
    use HasCrudActions {
        store as protected crudStore;
        update as protected crudUpdate;
    }

    protected $model = SpaBranch::class;

    protected $label = 'spabranch::spa_branches.spa_branch';

    protected $viewPath = 'spabranch::admin.spa_branches';

    protected $validation = SaveSpaBranchRequest::class;

    public function edit($id)
    {
        $spaBranch = $this->getEntity($id);

        if (is_module_enabled('Beautician')) {
            $spaBranch->load('beauticians');
        }

        $data = array_merge([
            'tabs' => TabManager::get($this->getModel()->getTable()),
            $this->getResourceName() => $spaBranch,
        ], $this->getFormData('edit', $id));

        return view("{$this->viewPath}.edit", $data);
    }

    public function store()
    {
        $this->normalizePositionOnRequest();
        $this->disableSearchSyncing();

        $entity = $this->getModel()->create(
            $this->getBranchPayload('store')
        );

        $this->syncBeauticians($entity);
        $this->searchable($entity);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => trans('admin::messages.resource_created', ['resource' => $this->getLabel()]),
            ]);
        }

        return redirect()->route('admin.spa_branches.index')
            ->withSuccess(trans('admin::messages.resource_created', ['resource' => $this->getLabel()]));
    }

    public function update($id)
    {
        $this->normalizePositionOnRequest();

        $entity = $this->getEntity($id);

        $this->disableSearchSyncing();

        $entity->update($this->getBranchPayload('update'));
        $this->syncBeauticians($entity);

        $entity->withoutEvents(function () use ($entity) {
            $entity->touch();
        });

        $this->searchable($entity);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]),
            ]);
        }

        return redirect()->route('admin.spa_branches.index')
            ->withSuccess(trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]));
    }

    protected function createFormData(): array
    {
        return $this->beauticianFormData();
    }

    protected function editFormData($id): array
    {
        return $this->beauticianFormData($this->getEntity($id));
    }

    private function normalizePositionOnRequest(): void
    {
        $position = request()->input('position');

        if ($position === '' || $position === null) {
            request()->merge(['position' => 0]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getBranchPayload(string $action): array
    {
        return $this->getRequest($action)
            ->except(array_merge(array_keys(request()->query()), ['beauticians']));
    }

    private function syncBeauticians(SpaBranch $spaBranch): void
    {
        if (! is_module_enabled('Beautician')) {
            return;
        }

        $spaBranch->beauticians()->sync(
            array_values(array_map(
                'intval',
                array_filter((array) request()->input('beauticians', []))
            ))
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function beauticianFormData(?SpaBranch $spaBranch = null): array
    {
        if (! is_module_enabled('Beautician')) {
            return [];
        }

        if ($spaBranch?->exists) {
            $spaBranch->loadMissing('beauticians');
        }

        $selectedBeauticianIds = $spaBranch?->exists
            ? $spaBranch->beauticians->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        return [
            'branchBeauticianOptions' => Beautician::query()
                ->with('files')
                ->where('is_active', true)
                ->orderBy('position')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
                ->map(fn (Beautician $beautician) => [
                    'id' => $beautician->id,
                    'name' => $beautician->name,
                    'job_title' => $beautician->job_title,
                    'profile_color' => $beautician->profile_color ?: '#6366f1',
                    'profile_image' => $beautician->profile_image->exists
                        ? $beautician->profile_image->path
                        : null,
                ])
                ->values()
                ->all(),
            'selectedBeauticianIds' => $selectedBeauticianIds,
        ];
    }
}
