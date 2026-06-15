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
                    <div class="tr-crm-pipeline__head-main">
                        <span class="tr-crm-pipeline__status-dot" aria-hidden="true"></span>
                        <div>
                            <h4>{{ $column['title'] }}</h4>
                            <span class="tr-crm-pipeline__badge">{{ $column['badge'] }}</span>
                        </div>
                    </div>
                    <span class="tr-crm-pipeline__count" data-pipeline-count="{{ $status }}">{{ count($column['items']) }}</span>
                </header>
                <ul class="tr-crm-pipeline__list" data-crm-list data-pipeline-list="{{ $status }}">
                    @forelse ($column['items'] as $booking)
                        <li
                            class="tr-crm-pipeline-card tr-crm-pipeline-card--{{ $status }} tr-crm-appointment tr-crm-appointment--clickable"
                            data-booking-id="{{ $booking['id'] ?? '' }}"
                            data-search="{{ strtolower(($booking['customer_name'] ?? '') . ' ' . ($booking['treatment_name'] ?? '') . ' ' . ($booking['beautician_name'] ?? '') . ' ' . ($booking['beautician_job_title'] ?? '') . ' ' . ($booking['source_label'] ?? '') . ' ' . ($booking['spa_branch_name'] ?? '') . ' ' . ($booking['appointment_date'] ?? '') . ' ' . ($booking['appointment_time_range'] ?? $booking['appointment_time'] ?? '')) }}"
                            role="button"
                            tabindex="0"
                        >
                            <header class="tr-crm-pipeline-card__head">
                                <div class="tr-crm-pipeline-card__schedule">
                                    <span class="tr-crm-pipeline-card__date">
                                        <i class="fa fa-calendar-o" aria-hidden="true"></i>
                                        <span>{{ $booking['appointment_date'] ?? TrLang::trans('admin.crm.ledger_unscheduled') }}</span>
                                    </span>
                                    <span class="tr-crm-pipeline-card__time">
                                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                                        <span>{{ $booking['appointment_time_range'] ?? $booking['appointment_time'] ?? TrLang::trans('admin.crm.ledger_time_tbc') }}</span>
                                    </span>
                                </div>
                                <span class="tr-crm-pipeline-card__status-dot" aria-hidden="true"></span>
                            </header>

                            <div class="tr-crm-pipeline-card__body">
                                <strong class="tr-crm-pipeline-card__customer">{{ $booking['customer_name'] ?? '—' }}</strong>

                                @if (! empty($booking['customer_history_label']) || ! empty($booking['loyalty_tier_name']))
                                    <p class="tr-crm-pipeline-card__insight">
                                        @if (! empty($booking['customer_history_label']))
                                            {{ $booking['customer_history_label'] }}
                                        @endif
                                        @if (! empty($booking['loyalty_tier_name']))
                                            @if (! empty($booking['customer_history_label']))
                                                ·
                                            @endif
                                            <i class="fa fa-star" aria-hidden="true"></i> {{ $booking['loyalty_tier_name'] }}
                                        @endif
                                    </p>
                                @endif

                                @if (! empty($booking['inline_alerts']))
                                    <div class="tr-crm-pipeline-card__alerts">
                                        @foreach ($booking['inline_alerts'] as $alert)
                                            <span class="tr-crm-pipeline-card__alert tr-crm-pipeline-card__alert--{{ $alert['level'] ?? 'info' }}">
                                                {{ $alert['label'] ?? '' }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <p class="tr-crm-pipeline-card__treatment">{{ $booking['treatment_name'] ?? '—' }}</p>

                                @if (! empty($booking['beautician_name']) && $booking['beautician_name'] !== '—')
                                    <div class="tr-crm-pipeline-card__specialist">
                                        <span
                                            class="tr-crm-pipeline-card__avatar"
                                            style="--tr-pipeline-avatar-color: {{ $booking['beautician_color'] ?? '#6366f1' }}"
                                            aria-hidden="true"
                                        >{{ $booking['beautician_initial'] ?? '?' }}</span>
                                        <span class="tr-crm-pipeline-card__specialist-text">
                                            {{ $booking['beautician_name'] }}
                                            @if (! empty($booking['beautician_job_title']))
                                                <em>{{ $booking['beautician_job_title'] }}</em>
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                @if (! empty($booking['source_label']) || ! empty($booking['spa_branch_name']))
                                    <div class="tr-crm-pipeline-card__meta">
                                        @if (! empty($booking['source_label']))
                                            <span class="tr-crm-pipeline-card__chip">{{ $booking['source_label'] }}</span>
                                        @endif
                                        @if (! empty($booking['spa_branch_name']))
                                            <span class="tr-crm-pipeline-card__chip tr-crm-pipeline-card__chip--branch">{{ $booking['spa_branch_name'] }}</span>
                                        @endif
                                    </div>
                                @endif

                                @if ($status === 'completed')
                                    <div class="tr-crm-pipeline-card__finished">
                                        <span class="tr-crm-pipeline-card__done">
                                            <i class="fa fa-check-circle" aria-hidden="true"></i>
                                            {{ TrLang::trans('admin.crm.pipeline_done') }}
                                        </span>
                                        @if (! empty($booking['total_formatted']))
                                            <strong class="tr-crm-pipeline-card__price">{{ $booking['total_formatted'] }}</strong>
                                        @endif
                                        @if (! empty($booking['payment_is_outstanding']) && ! empty($booking['payment_status_label']))
                                            <span class="tr-crm-pipeline-card__payment tr-crm-pipeline-card__payment--{{ $booking['payment_status'] ?? 'pending' }}">
                                                {{ TrLang::trans('admin.crm.payment_chip', ['status' => $booking['payment_status_label']]) }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            @if ($column['action'] === 'start' && ! empty($booking['next_status']))
                                <footer class="tr-crm-pipeline-card__footer">
                                    <button
                                        type="button"
                                        class="tr-crm-pipeline-card__cta"
                                        data-pipeline-action="start"
                                        data-booking-id="{{ $booking['id'] }}"
                                        data-next-status="{{ $booking['next_status'] }}"
                                    >
                                        <i class="fa fa-play" aria-hidden="true"></i>
                                        {{ TrLang::trans('admin.crm.action_start_treatment') }}
                                    </button>
                                </footer>
                            @elseif ($column['action'] === 'complete' && ! empty($booking['next_status']))
                                <footer class="tr-crm-pipeline-card__footer">
                                    <button
                                        type="button"
                                        class="tr-crm-pipeline-card__cta tr-crm-pipeline-card__cta--finish"
                                        data-pipeline-action="complete"
                                        data-booking-id="{{ $booking['id'] }}"
                                        data-next-status="{{ $booking['next_status'] }}"
                                    >
                                        <i class="fa fa-check" aria-hidden="true"></i>
                                        {{ TrLang::trans('admin.crm.action_complete_checkout') }}
                                    </button>
                                </footer>
                            @endif

                            @if (! empty($booking['order_url']) || ! empty($booking['can_reschedule_manual']))
                                <div class="tr-crm-pipeline-card__links">
                                    @if (! empty($booking['order_url']))
                                        <a
                                            href="{{ $booking['order_url'] }}"
                                            class="tr-crm-pipeline-card__link"
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
                                            class="tr-crm-pipeline-card__link"
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
