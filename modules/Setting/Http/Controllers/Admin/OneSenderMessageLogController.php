<?php

namespace Modules\Setting\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\User\Entities\OneSenderMessageLog;

class OneSenderMessageLogController
{
    public function index(Request $request)
    {
        $logs = $this->filteredQuery($request)->paginate(30)->withQueryString();

        $statuses = [
            OneSenderMessageLog::STATUS_SENT,
            OneSenderMessageLog::STATUS_FAILED,
            OneSenderMessageLog::STATUS_SKIPPED_DUPLICATE,
            OneSenderMessageLog::STATUS_SKIPPED_PAUSED,
            OneSenderMessageLog::STATUS_SKIPPED_DISABLED,
            OneSenderMessageLog::STATUS_SKIPPED_CANCELLED,
        ];

        $filteredCount = $this->filteredQuery($request)->count();

        return view('setting::admin.onesender_logs.index', compact('logs', 'statuses', 'filteredCount'));
    }


    public function destroy(OneSenderMessageLog $log): RedirectResponse
    {
        $log->delete();

        return back()->with('success', trans('setting::settings.onesender_logs.deleted_one'));
    }


    public function destroyFiltered(Request $request): RedirectResponse
    {
        $count = $this->filteredQuery($request)->delete();

        return redirect()
            ->route('admin.onesender_logs.index')
            ->with('success', trans('setting::settings.onesender_logs.deleted_filtered', ['count' => $count]));
    }


    public function destroyAll(): RedirectResponse
    {
        $count = OneSenderMessageLog::query()->count();
        OneSenderMessageLog::query()->delete();

        return redirect()
            ->route('admin.onesender_logs.index')
            ->with('success', trans('setting::settings.onesender_logs.deleted_all', ['count' => $count]));
    }


    private function filteredQuery(Request $request): Builder
    {
        $query = OneSenderMessageLog::query()->orderByDesc('id');

        if ($status = trim((string) $request->input('status'))) {
            $query->where('status', $status);
        }

        if ($recipient = trim((string) $request->input('recipient'))) {
            $query->where('recipient', 'like', '%' . $recipient . '%');
        }

        if ($source = trim((string) $request->input('source'))) {
            $query->where('source', 'like', '%' . $source . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        return $query;
    }
}
