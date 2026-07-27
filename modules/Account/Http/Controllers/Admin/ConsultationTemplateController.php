<?php

namespace Modules\Account\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Account\Entities\ConsultationFormTemplate;
use Modules\Account\Http\Requests\SaveConsultationTemplateRequest;
use Modules\Account\Services\ConsultationFormService;
use Modules\Account\Services\ConsultationTemplateNormalizer;

class ConsultationTemplateController extends Controller
{
    public function index(ConsultationFormService $forms): View
    {
        $templates = ConsultationFormTemplate::query()
            ->withCount([
                'submissions',
                'submissions as pending_count' => fn ($query) => $query
                    ->whereNull('submitted_at')
                    ->whereNull('revoked_at'),
                'submissions as completed_count' => fn ($query) => $query->whereNotNull('submitted_at'),
            ])
            ->latest('updated_at')
            ->get();

        $recentRequests = $forms->recentRequests();

        return view('product::admin.consultation_forms.index', compact('templates', 'recentRequests'));
    }

    public function edit(ConsultationFormTemplate $template): View
    {
        return view('product::admin.consultation_forms.edit', compact('template'));
    }

    public function update(
        SaveConsultationTemplateRequest $request,
        ConsultationFormTemplate $template,
        ConsultationTemplateNormalizer $normalizer
    ): RedirectResponse {
        $next = [
            'name' => trim((string) $request->input('name')),
            'title' => trim((string) $request->input('title')),
            'intro' => trim((string) $request->input('intro')) ?: null,
            'consent_text' => trim((string) $request->input('consent')),
            'questions' => $normalizer->questions($request->input('questions', [])),
            'is_active' => $request->boolean('is_active'),
        ];

        $changed = collect($next)->except('is_active')->some(
            fn ($value, $key) => $template->getAttribute($key) != $value
        );

        $next['version'] = $changed
            ? max(1, (int) $template->version) + 1
            : max(1, (int) $template->version);

        $template->update($next);

        return redirect()
            ->route('admin.consultation_forms.edit', $template)
            ->withSuccess(trans('product::consultation_forms.messages.saved'));
    }
}
