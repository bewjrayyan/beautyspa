<?php

namespace Modules\Beautician\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Beautician\Entities\BeauticianJobTitle;
use Modules\Beautician\Http\Requests\SaveBeauticianJobTitleRequest;

class BeauticianJobTitleController
{
    use HasCrudActions;

    protected $model = BeauticianJobTitle::class;

    protected $label = 'beautician::job_titles.job_title';

    protected $viewPath = 'beautician::admin.job_titles';

    protected $validation = SaveBeauticianJobTitleRequest::class;


    public function destroy($ids): RedirectResponse
    {
        $ids = array_filter(explode(',', (string) $ids));

        $blocked = BeauticianJobTitle::query()
            ->whereIn('id', $ids)
            ->get()
            ->filter(fn (BeauticianJobTitle $jobTitle) => $jobTitle->isInUse())
            ->pluck('name')
            ->all();

        if ($blocked !== []) {
            return redirect()->route('admin.beautician_job_titles.index')
                ->withError(trans('beautician::job_titles.messages.delete_blocked', [
                    'titles' => implode(', ', $blocked),
                ]));
        }

        BeauticianJobTitle::query()
            ->whereIn('id', $ids)
            ->delete();

        return redirect()->route('admin.beautician_job_titles.index')
            ->withSuccess(trans('admin::messages.resource_deleted', [
                'resource' => trans('beautician::job_titles.job_title'),
            ]));
    }
}
