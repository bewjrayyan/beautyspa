<?php

namespace Modules\Setting\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\User\Entities\OneSenderOutboundMessage;
use Modules\User\Services\OneSenderOutboundQueueService;

class OneSenderOutboundQueueController
{
    public function index(Request $request)
    {
        $statuses = [
            OneSenderOutboundMessage::STATUS_PENDING,
            OneSenderOutboundMessage::STATUS_PROCESSING,
            OneSenderOutboundMessage::STATUS_SENT,
            OneSenderOutboundMessage::STATUS_FAILED,
            OneSenderOutboundMessage::STATUS_CANCELLED,
        ];

        $messages = $this->filteredQuery($request)->paginate(30)->withQueryString();

        $statusCounts = array_fill_keys($statuses, 0);

        foreach (OneSenderOutboundMessage::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status') as $status => $count) {
            if (array_key_exists($status, $statusCounts)) {
                $statusCounts[$status] = (int) $count;
            }
        }

        $pendingCount = app(OneSenderOutboundQueueService::class)->pendingCount();
        $filteredCount = $messages->total();
        $deletableFilteredCount = $this->deletableQuery($this->filteredQuery($request))->count();
        $deletableTotalCount = $this->deletableQuery(OneSenderOutboundMessage::query())->count();
        $hasActiveFilters = request()->hasAny(['status', 'recipient', 'source'])
            && collect(['status', 'recipient', 'source'])->contains(fn (string $key) => trim((string) request($key)) !== '');

        return view('setting::admin.onesender_queue.index', compact(
            'messages',
            'statuses',
            'statusCounts',
            'pendingCount',
            'filteredCount',
            'deletableFilteredCount',
            'deletableTotalCount',
            'hasActiveFilters',
        ));
    }


    public function cancel(OneSenderOutboundMessage $message): RedirectResponse
    {
        $cancelled = app(OneSenderOutboundQueueService::class)->cancel($message);

        if (! $cancelled) {
            return back()->with('error', trans('setting::settings.onesender_queue.cancel_failed'));
        }

        return back()->with('success', trans('setting::settings.onesender_queue.cancelled_one'));
    }


    public function cancelAll(): RedirectResponse
    {
        $count = app(OneSenderOutboundQueueService::class)->cancelAllPending();

        return back()->with('success', trans('setting::settings.onesender_queue.cancelled_all', ['count' => $count]));
    }


    public function destroy(OneSenderOutboundMessage $message): RedirectResponse
    {
        $deleted = app(OneSenderOutboundQueueService::class)->deleteMessage($message);

        if (! $deleted) {
            return back()->with('error', trans('setting::settings.onesender_queue.delete_failed'));
        }

        return back()->with('success', trans('setting::settings.onesender_queue.deleted_one'));
    }


    public function destroyFiltered(Request $request): RedirectResponse
    {
        $count = app(OneSenderOutboundQueueService::class)->deleteDeletableFromQuery(
            $this->deletableQuery($this->filteredQuery($request))
        );

        return redirect()
            ->route('admin.onesender_queue.index', $request->only(['status', 'recipient', 'source']))
            ->with('success', trans('setting::settings.onesender_queue.deleted_filtered', ['count' => $count]));
    }


    public function destroyAll(): RedirectResponse
    {
        $count = app(OneSenderOutboundQueueService::class)->deleteAllDeletable();

        return redirect()
            ->route('admin.onesender_queue.index')
            ->with('success', trans('setting::settings.onesender_queue.deleted_all', ['count' => $count]));
    }


    public function processDue(): RedirectResponse
    {
        $processed = app(OneSenderOutboundQueueService::class)->processDueBatch();

        return back()->with('success', trans('setting::settings.onesender_queue.processed_due', [
            'count' => $processed,
        ]));
    }


    private function filteredQuery(Request $request): Builder
    {
        $query = OneSenderOutboundMessage::query()->orderByDesc('id');

        if ($status = trim((string) $request->query('status', $request->input('status')))) {
            $query->where('status', $status);
        }

        if ($recipient = trim((string) $request->query('recipient', $request->input('recipient')))) {
            $query->where('recipient', 'like', '%' . $recipient . '%');
        }

        if ($source = trim((string) $request->query('source', $request->input('source')))) {
            $query->where('source', 'like', '%' . $source . '%');
        }

        return $query;
    }


    private function deletableQuery(Builder $query): Builder
    {
        return (clone $query)->whereIn('status', [
            OneSenderOutboundMessage::STATUS_SENT,
            OneSenderOutboundMessage::STATUS_FAILED,
            OneSenderOutboundMessage::STATUS_CANCELLED,
        ]);
    }
}
