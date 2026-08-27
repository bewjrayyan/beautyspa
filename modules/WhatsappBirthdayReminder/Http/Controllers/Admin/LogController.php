<?php

namespace Modules\WhatsappBirthdayReminder\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\WhatsappBirthdayReminder\Entities\BirthdayReminderLog;
use Modules\WhatsappBirthdayReminder\Services\BirthdayReminderService;

class LogController
{
    public function index(): View
    {
        $logs = BirthdayReminderLog::query()
            ->with('user')
            ->latest()
            ->paginate(25);

        return view('whatsappbirthday::admin.logs.index', compact('logs'));
    }


    public function send(BirthdayReminderService $service): RedirectResponse
    {
        $stats = $service->processToday(force: true);

        return redirect()
            ->route('admin.whatsapp_birthday.index')
            ->with('success', trans('whatsappbirthday::messages.command_summary', $stats));
    }
}
