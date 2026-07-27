<div class="admin-consultation-tab">
    <div class="admin-consultation-intro">
        <div>
            <h3>{{ trans('account::consultation.admin.title') }}</h3>
            <p>{{ trans('account::consultation.admin.lead') }}</p>
        </div>
        <div class="admin-consultation-stats">
            <span><strong>{{ $pendingForms->count() }}</strong> {{ trans('account::consultation.admin.pending') }}</span>
            <span><strong>{{ $submissions->total() }}</strong> {{ trans('account::consultation.admin.completed') }}</span>
        </div>
    </div>

    <section class="admin-consultation-section">
        <h4>{{ trans('account::consultation.pending_title') }}</h4>

        @forelse ($pendingForms as $consultation)
            @php $consultationContext = app(\Modules\Account\Services\ConsultationContextService::class)->forDisplay($consultation); @endphp
            <article class="admin-consultation-row admin-consultation-row--pending">
                <div>
                    <strong>{{ $consultation->form_title }}</strong>
                    <span>
                        {{ $consultationContext['treatment_name'] ?: '—' }}
                        @if ($consultationContext['beautician_name'] ?? null) · {{ $consultationContext['beautician_name'] }} @endif
                    </span>
                </div>
                <span class="admin-consultation-badge is-pending">{{ trans('account::consultation.pending') }}</span>
            </article>
        @empty
            <div class="admin-consultation-empty">{{ trans('account::consultation.all_complete') }}</div>
        @endforelse
    </section>

    <section class="admin-consultation-section">
        <h4>{{ trans('account::consultation.history_title') }}</h4>

        @forelse ($submissions as $submission)
            <article class="admin-consultation-row">
                <div>
                    <strong>{{ $submission->form_title }}</strong>
                    <span>
                        {{ trans('account::consultation.submitted', ['date' => $submission->submitted_at?->format('d M Y, H:i')]) }}
                        · {{ trans('account::consultation.version', ['version' => $submission->template_version]) }}
                    </span>
                </div>
                <div class="admin-consultation-row__actions">
                    <span class="admin-consultation-badge is-complete">{{ trans('account::consultation.complete') }}</span>
                    <a
                        class="btn btn-default btn-sm"
                        href="{{ route('admin.users.consultations.download', $submission) }}"
                    >
                        <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                        {{ trans('account::consultation.download_pdf') }}
                    </a>
                </div>
            </article>
        @empty
            <div class="admin-consultation-empty">{{ trans('account::consultation.no_history') }}</div>
        @endforelse

        @if ($submissions->hasPages())
            <div class="pagination-wrapper">{{ $submissions->links() }}</div>
        @endif
    </section>

    <section class="admin-consultation-section admin-consultation-share">
        <h4>{{ trans('account::consultation.admin.share_title') }}</h4>
        <p>{{ trans('account::consultation.admin.share_help') }}</p>
        <div class="admin-consultation-empty admin-consultation-empty--action">
            <span class="admin-consultation-empty__icon" aria-hidden="true"><i class="fa fa-calendar-check-o"></i></span>
            <div>
                <strong>{{ trans('account::consultation.admin.no_templates') }}</strong>
                <p>{{ trans('account::consultation.admin.configure_hint') }}</p>
            </div>
            <a class="btn btn-primary btn-sm" href="{{ route('admin.treatment_reservations.index', ['view' => 'kanban']) }}">
                {{ trans('treatmentreservation::admin.kanban.title') }}
                <i class="fa fa-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </section>
</div>
