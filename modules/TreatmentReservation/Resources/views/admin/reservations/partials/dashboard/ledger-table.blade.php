@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
@endphp

@php
    $ledger = $ledger ?? [];
    $ledgerCount = $ledgerCount ?? count($ledger);
@endphp

<section class="tr-crm-panel tr-crm-panel--ledger">
    <header class="tr-crm-panel__head">
        <div>
            <h3 class="tr-crm-panel__title">{{ TrLang::trans('admin.crm.ledger_title') }}</h3>
            <p class="tr-crm-panel__lead">
                {{ TrLang::trans('admin.crm.ledger_displaying', ['count' => $ledgerCount]) }}
            </p>
        </div>
    </header>

    <div class="tr-crm-ledger-wrap">
        <table class="tr-crm-ledger">
            <thead>
                <tr>
                    <th>{{ TrLang::trans('admin.crm.ledger_client') }}</th>
                    <th>{{ TrLang::trans('admin.crm.ledger_treatment') }}</th>
                    <th>{{ TrLang::trans('admin.crm.ledger_datetime') }}</th>
                    <th>{{ TrLang::trans('admin.crm.ledger_specialist') }}</th>
                    <th class="tr-crm-ledger__amount">{{ TrLang::trans('admin.crm.ledger_subtotal') }}</th>
                    <th class="tr-crm-ledger__status">{{ TrLang::trans('admin.crm.ledger_status') }}</th>
                </tr>
            </thead>
            <tbody data-crm-list>
                @forelse ($ledger as $row)
                    <tr
                        class="tr-crm-ledger__row tr-crm-ledger__row--clickable"
                        data-booking-id="{{ $row['id'] ?? '' }}"
                        data-search="{{ strtolower(($row['customer_name'] ?? '') . ' ' . ($row['treatment_name'] ?? '') . ' ' . ($row['treatment_subtitle'] ?? '') . ' ' . ($row['beautician_name'] ?? '') . ' ' . ($row['beautician_job_title'] ?? '') . ' ' . ($row['source_label'] ?? '') . ' ' . ($row['spa_branch_name'] ?? '') . ' ' . ($row['status_label'] ?? '')) }}"
                        role="button"
                        tabindex="0"
                    >
                        <td>
                            <div class="tr-crm-ledger__client">
                                <span
                                    class="tr-crm-ledger__avatar tr-crm-ledger__avatar--client"
                                    style="background-color: {{ $row['customer_color'] ?? '#4a0e2e' }}"
                                >{{ $row['customer_initial'] ?? '?' }}</span>
                                <div class="tr-crm-ledger__client-text">
                                    <strong>
                                        <button
                                            type="button"
                                            class="tr-crm-ledger__customer-link"
                                            data-customer-profile
                                            data-booking-id="{{ $row['id'] ?? '' }}"
                                            onclick="event.stopPropagation()"
                                        >{{ $row['customer_name'] ?? '—' }}</button>
                                    </strong>
                                    <span>{{ $row['customer_phone'] ?? TrLang::trans('admin.crm.ledger_no_phone') }}</span>
                                    @if (! empty($row['customer_history_label']) || ! empty($row['loyalty_tier_name']))
                                        <span class="tr-crm-ledger__insight">
                                            @if (! empty($row['customer_history_label']))
                                                {{ $row['customer_history_label'] }}
                                            @endif
                                            @if (! empty($row['loyalty_tier_name']))
                                                @if (! empty($row['customer_history_label']))
                                                    ·
                                                @endif
                                                <i class="fa fa-star" aria-hidden="true"></i> {{ $row['loyalty_tier_name'] }}
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="tr-crm-ledger__treatment">
                            <strong>{{ $row['treatment_name'] ?? '—' }}</strong>
                            @if (! empty($row['treatment_subtitle']))
                                <span class="tr-crm-ledger__category">{{ $row['treatment_subtitle'] }}</span>
                            @endif
                        </td>
                        <td class="tr-crm-ledger__datetime">
                            <strong>{{ $row['appointment_date'] ?? '—' }}</strong>
                            <span>{{ $row['appointment_time_range'] ?? $row['appointment_time'] ?? '—' }}</span>
                            @if (! empty($row['source_label']) || ! empty($row['spa_branch_name']))
                                <span class="tr-crm-ledger__meta">
                                    @if (! empty($row['source_label']))
                                        <span class="tr-crm-ledger__chip tr-crm-ledger__chip--source">{{ $row['source_label'] }}</span>
                                    @endif
                                    @if (! empty($row['spa_branch_name']))
                                        <span class="tr-crm-ledger__chip tr-crm-ledger__chip--branch">{{ $row['spa_branch_name'] }}</span>
                                    @endif
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="tr-crm-ledger__specialist{{ empty($row['beautician_assigned']) ? ' tr-crm-ledger__specialist--unassigned' : '' }}">
                                @if (! empty($row['beautician_avatar']))
                                    <img
                                        src="{{ $row['beautician_avatar'] }}"
                                        alt="{{ $row['beautician_name'] ?? '' }}"
                                        class="tr-crm-ledger__avatar-img tr-crm-ledger__avatar-img--sm"
                                    >
                                @else
                                    <span
                                        class="tr-crm-ledger__avatar tr-crm-ledger__avatar--sm"
                                        style="background-color: {{ $row['beautician_color'] ?? '#6d2847' }}"
                                    >{{ $row['beautician_initial'] ?? '?' }}</span>
                                @endif
                                <span class="tr-crm-ledger__specialist-text">
                                    <span class="tr-crm-ledger__specialist-name">{{ $row['beautician_name'] ?? TrLang::trans('admin.crm.ledger_unassigned') }}</span>
                                    @if (! empty($row['beautician_job_title']))
                                        <span class="tr-crm-ledger__specialist-role">{{ $row['beautician_job_title'] }}</span>
                                    @endif
                                </span>
                            </span>
                        </td>
                        <td class="tr-crm-ledger__amount">{{ $row['total_formatted'] ?? '—' }}</td>
                        <td class="tr-crm-ledger__status">
                            <span
                                class="tr-crm-status-pill tr-crm-status-pill--{{ $row['status'] ?? 'pending' }}"
                                style="--tr-status-color: {{ $row['status_accent'] ?? '#94a3b8' }}"
                            >
                                {{ $row['status_label'] ?? '—' }}
                            </span>
                            @if (! empty($row['inline_alerts']))
                                <span class="tr-crm-ledger__alerts">
                                    @foreach ($row['inline_alerts'] as $alert)
                                        <span class="tr-crm-ledger__alert tr-crm-ledger__alert--{{ $alert['level'] ?? 'info' }}">
                                            {{ $alert['label'] ?? '' }}
                                        </span>
                                    @endforeach
                                </span>
                            @endif
                            @if (! empty($row['order_url']) || ! empty($row['can_reschedule_manual']))
                                <span class="tr-crm-ledger__quick-actions">
                                    @if (! empty($row['order_url']))
                                        <a
                                            href="{{ $row['order_url'] }}"
                                            class="tr-crm-ledger__quick-action"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            onclick="event.stopPropagation()"
                                        >
                                            {{ TrLang::trans('admin.crm.action_view_order') }}
                                        </a>
                                    @endif
                                    @if (! empty($row['can_reschedule_manual']))
                                        <button
                                            type="button"
                                            class="tr-crm-ledger__quick-action"
                                            data-ledger-reschedule
                                            data-booking-id="{{ $row['id'] }}"
                                            onclick="event.stopPropagation()"
                                        >
                                            {{ TrLang::trans('admin.crm.action_reschedule') }}
                                        </button>
                                    @endif
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="tr-crm-ledger__empty">
                            {{ TrLang::trans('admin.crm.ledger_empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
