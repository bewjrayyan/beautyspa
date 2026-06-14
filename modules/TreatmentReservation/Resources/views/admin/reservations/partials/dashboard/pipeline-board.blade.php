@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
@endphp

@php
    $pipeline = $pipeline ?? ['pending' => [], 'in_progress' => [], 'completed' => []];
    $queueCount = $queueCount ?? count($pipeline['all'] ?? []);
    $filterDateLabel = $filterDateLabel ?? '';
    $dateFilter = $dateFilter ?? 'today';
    $queueLabel = match ($dateFilter) {
        'all' => TrLang::trans('admin.crm.pipeline_queue_all', ['count' => $queueCount]),
        'today' => TrLang::trans('admin.crm.pipeline_queue', ['count' => $queueCount]),
        default => TrLang::trans('admin.crm.pipeline_queue_date', ['count' => $queueCount, 'date' => $filterDateLabel]),
    };
    $columns = [
        'pending' => [
            'title' => TrLang::trans('admin.crm.pipeline_waiting'),
            'badge' => TrLang::trans('admin.crm.pipeline_pending_badge'),
            'items' => $pipeline['pending'] ?? [],
            'action' => 'start',
        ],
        'in_progress' => [
            'title' => TrLang::trans('admin.crm.pipeline_active'),
            'badge' => TrLang::trans('admin.crm.pipeline_active_badge'),
            'items' => $pipeline['in_progress'] ?? [],
            'action' => 'complete',
        ],
        'completed' => [
            'title' => TrLang::trans('admin.crm.pipeline_finished'),
            'badge' => TrLang::trans('admin.crm.pipeline_finished_badge'),
            'items' => $pipeline['completed'] ?? [],
            'action' => null,
        ],
    ];
@endphp

<section class="tr-crm-panel tr-crm-panel--pipeline">
    <header class="tr-crm-panel__head">
        <div>
            <h3 class="tr-crm-panel__title">{{ TrLang::trans('admin.crm.pipeline_title') }}</h3>
            <p class="tr-crm-panel__lead">{{ TrLang::trans('admin.crm.pipeline_lead_long') }}</p>
        </div>
        <span class="tr-crm-pipeline__queue-badge">
            {{ $queueLabel }}
        </span>
    </header>

    <div class="tr-crm-pipeline" data-crm-pipeline>
        @foreach ($columns as $status => $column)
            <div class="tr-crm-pipeline__column tr-crm-pipeline__column--{{ $status }}" data-pipeline-column="{{ $status }}">
                <header class="tr-crm-pipeline__head">
                    <div>
                        <h4>{{ $column['title'] }}</h4>
                        <span class="tr-crm-pipeline__badge">{{ $column['badge'] }}</span>
                    </div>
                    <span class="tr-crm-pipeline__count" data-pipeline-count="{{ $status }}">{{ count($column['items']) }}</span>
                </header>
                <ul class="tr-crm-pipeline__list" data-crm-list data-pipeline-list="{{ $status }}">
                    @forelse ($column['items'] as $booking)
                        <li
                            class="tr-crm-pipeline-card tr-crm-appointment tr-crm-appointment--clickable"
                            data-booking-id="{{ $booking['id'] ?? '' }}"
                            data-search="{{ strtolower(($booking['customer_name'] ?? '') . ' ' . ($booking['treatment_name'] ?? '') . ' ' . ($booking['beautician_name'] ?? '') . ' ' . ($booking['beautician_job_title'] ?? '') . ' ' . ($booking['source_label'] ?? '') . ' ' . ($booking['spa_branch_name'] ?? '')) }}"
                            role="button"
                            tabindex="0"
                        >
                            @if ($status === 'pending')
                                <div class="tr-crm-pipeline-card__time">{{ $booking['appointment_time_range'] ?? $booking['appointment_time'] ?? '—' }}</div>
                            @endif

                            <div class="tr-crm-pipeline-card__main">
                                <strong>{{ $booking['customer_name'] ?? '—' }}</strong>
                                @if (! empty($booking['customer_history_label']) || ! empty($booking['loyalty_tier_name']))
                                    <span class="tr-crm-pipeline-card__insight">
                                        @if (! empty($booking['customer_history_label']))
                                            {{ $booking['customer_history_label'] }}
                                        @endif
                                        @if (! empty($booking['loyalty_tier_name']))
                                            @if (! empty($booking['customer_history_label']))
                                                ·
                                            @endif
                                            <i class="fa fa-star" aria-hidden="true"></i> {{ $booking['loyalty_tier_name'] }}
                                        @endif
                                    </span>
                                @endif
                                @if (! empty($booking['inline_alerts']))
                                    <span class="tr-crm-pipeline-card__alerts">
                                        @foreach ($booking['inline_alerts'] as $alert)
                                            <span class="tr-crm-pipeline-card__alert tr-crm-pipeline-card__alert--{{ $alert['level'] ?? 'info' }}">
                                                {{ $alert['label'] ?? '' }}
                                            </span>
                                        @endforeach
                                    </span>
                                @endif
                                <span>{{ $booking['treatment_name'] ?? '—' }}</span>
                                @if (! empty($booking['source_label']) || ! empty($booking['spa_branch_name']))
                                    <span class="tr-crm-pipeline-card__meta">
                                        @if (! empty($booking['source_label']))
                                            <span class="tr-crm-pipeline-card__chip">{{ $booking['source_label'] }}</span>
                                        @endif
                                        @if (! empty($booking['spa_branch_name']))
                                            <span class="tr-crm-pipeline-card__chip tr-crm-pipeline-card__chip--branch">{{ $booking['spa_branch_name'] }}</span>
                                        @endif
                                    </span>
                                @endif
                                @if ($status === 'in_progress' && ! empty($booking['beautician_name']))
                                    <span class="tr-crm-pipeline-card__specialist">
                                        {{ TrLang::trans('admin.crm.pipeline_by_specialist', ['name' => $booking['beautician_name']]) }}
                                        @if (! empty($booking['beautician_job_title']))
                                            · {{ $booking['beautician_job_title'] }}
                                        @endif
                                    </span>
                                @endif
                                @if ($status === 'completed')
                                    <span class="tr-crm-pipeline-card__done">{{ TrLang::trans('admin.crm.pipeline_done') }}</span>
                                    @if (! empty($booking['total_formatted']))
                                        <span class="tr-crm-pipeline-card__price">{{ $booking['total_formatted'] }}</span>
                                    @endif
                                    @if (! empty($booking['payment_status_label']))
                                        <span class="tr-crm-pipeline-card__payment">{{ $booking['payment_status_label'] }}</span>
                                    @endif
                                @endif
                            </div>

                            @if ($column['action'] === 'start' && ! empty($booking['next_status']))
                                <button
                                    type="button"
                                    class="tr-crm-pipeline-card__action"
                                    data-pipeline-action="start"
                                    data-booking-id="{{ $booking['id'] }}"
                                    data-next-status="{{ $booking['next_status'] }}"
                                >
                                    {{ TrLang::trans('admin.crm.action_start_treatment') }}
                                </button>
                            @elseif ($column['action'] === 'complete' && ! empty($booking['next_status']))
                                <button
                                    type="button"
                                    class="tr-crm-pipeline-card__action tr-crm-pipeline-card__action--checkout"
                                    data-pipeline-action="complete"
                                    data-booking-id="{{ $booking['id'] }}"
                                    data-next-status="{{ $booking['next_status'] }}"
                                >
                                    {{ TrLang::trans('admin.crm.action_complete_checkout') }}
                                </button>
                            @endif

                            @if (! empty($booking['order_url']) || ! empty($booking['can_reschedule_manual']))
                                <div class="tr-crm-pipeline-card__quick-actions">
                                    @if (! empty($booking['order_url']))
                                        <a
                                            href="{{ $booking['order_url'] }}"
                                            class="tr-crm-pipeline-card__quick-action"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            onclick="event.stopPropagation()"
                                        >
                                            {{ TrLang::trans('admin.crm.action_view_order') }}
                                        </a>
                                    @endif
                                    @if (! empty($booking['can_reschedule_manual']))
                                        <button
                                            type="button"
                                            class="tr-crm-pipeline-card__quick-action"
                                            data-pipeline-reschedule
                                            data-booking-id="{{ $booking['id'] }}"
                                        >
                                            {{ TrLang::trans('admin.crm.action_reschedule') }}
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </li>
                    @empty
                        <li class="tr-crm-empty tr-crm-empty--inline">
                            <p>{{ TrLang::trans('admin.kanban.empty') }}</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        @endforeach
    </div>
</section>
