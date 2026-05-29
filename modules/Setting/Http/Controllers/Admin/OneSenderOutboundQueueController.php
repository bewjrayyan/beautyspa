<?php

namespace Modules\Setting\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\User\Entities\OneSenderOutboundMessage;
use Modules\User\Services\OneSenderOutboundQueueService;

class OneSenderOutboundQueueController
{
    public function index(Request $request)
    {
        $query = OneSenderOutboundMessage::query()->orderByDesc('id');

        if ($status = trim((string) $request->query('status'))) {
            $query->where('status', $status);
        }

        if ($recipient = trim((string) $request->query('recipient'))) {
            $query->where('recipient', 'like', '%' . $recipient . '%');
        }

        if ($source = trim((string) $request->query('source'))) {
            $query->where('source', 'like', '%' . $source . '%');
        }

        $messages = $query->paginate(30)->withQueryString();

        $statuses = [
            OneSenderOutboundMessage::STATUS_PENDING,
            OneSenderOutboundMessage::STATUS_PROCESSING,
            OneSenderOutboundMessage::STATUS_SENT,
            OneSenderOutboundMessage::STATUS_FAILED,
            OneSenderOutboundMessage::STATUS_CANCELLED,
        ];

        $pendingCount = app(OneSenderOutboundQueueService::class)->pendingCount();

        return view('setting::admin.onesender_queue.index', compact('messages', 'statuses', 'pendingCount'));
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
}
