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
                    <th>{{ TrLang::trans('admin.crm.ledger_appointment') }}</th>
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
                        data-search="{{ strtolower(($row['customer_name'] ?? '') . ' ' . ($row['customer_phone'] ?? '') . ' ' . ($row['treatment_name'] ?? '') . ' ' . ($row['treatment_subtitle'] ?? '') . ' ' . ($row['appointment_date'] ?? '') . ' ' . ($row['appointment_time_range'] ?? $row['appointment_time'] ?? '') . ' ' . ($row['beautician_name'] ?? '') . ' ' . ($row['beautician_job_title'] ?? '') . ' ' . ($row['source_label'] ?? '') . ' ' . ($row['spa_branch_name'] ?? '') . ' ' . ($row['status_label'] ?? '') . ' ' . ($row['total_formatted'] ?? '') . ' ' . ($row['id'] ?? '')) }}"
                        role="button"
                        tabindex="0"
                        aria-label="{{ ($row['customer_name'] ?? TrLang::trans('admin.crm.ledger_unknown_client')) . ', ' . ($row['treatment_name'] ?? TrLang::trans('admin.crm.ledger_unknown_treatment')) }}"
                    >
                        <td class="tr-crm-ledger__cell tr-crm-ledger__cell--client">
                            <div class="tr-crm-ledger__client">
                                <span
                                    class="tr-crm-ledger__avatar tr-crm-ledger__avatar--client"
                                    style="background-color: {{ $row['customer_color'] ?? '#4a0e2e' }}"
                                >{{ $row['customer_initial'] ?? '?' }}</span>
                                <div class="tr-crm-ledger__client-text">
                                    <button
                                        type="button"
                                        class="tr-crm-ledger__customer-link"
                                        data-customer-profile
                                        data-booking-id="{{ $row['id'] ?? '' }}"
                                        onclick="event.stopPropagation()"
                                    >{{ $row['customer_name'] ?? TrLang::trans('admin.crm.ledger_unknown_client') }}</button>
                                    <span class="tr-crm-ledger__client-phone">{{ $row['customer_phone'] ?? TrLang::trans('admin.crm.ledger_no_phone') }}</span>
                                    @if (! empty($row['customer_history_label']) || ! empty($row['loyalty_tier_name']))
                                        <span class="tr-crm-ledger__chip-row">
                                            @if (! empty($row['customer_history_label']))
                                                <span class="tr-crm-ledger__chip">{{ $row['customer_history_label'] }}</span>
                                            @endif
                                            @if (! empty($row['loyalty_tier_name']))
                                                <span class="tr-crm-ledger__chip tr-crm-ledger__chip--loyalty">
                                                    <i class="fa fa-star" aria-hidden="true"></i> {{ $row['loyalty_tier_name'] }}
                                                </span>
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="tr-crm-ledger__cell tr-crm-ledger__cell--appointment">
                            <div class="tr-crm-ledger__appointment">
                                <strong class="tr-crm-ledger__treatment-name">{{ $row['treatment_name'] ?? TrLang::trans('admin.crm.ledger_unknown_treatment') }}</strong>
                                @if (! empty($row['treatment_subtitle']))
                                    <span class="tr-crm-ledger__treatment-sub">{{ $row['treatment_subtitle'] }}</span>
                                @endif
                                <span class="tr-crm-ledger__schedule">
                                    <i class="fa fa-calendar-o" aria-hidden="true"></i>
                                    {{ $row['appointment_date'] ?? TrLang::trans('admin.crm.ledger_unscheduled') }}
                                    <span class="tr-crm-ledger__schedule-sep">·</span>
                                    {{ $row['appointment_time_range'] ?? $row['appointment_time'] ?? TrLang::trans('admin.crm.ledger_time_tbc') }}
                                </span>
                                @if (! empty($row['source_label']) || ! empty($row['spa_branch_name']))
                                    <span class="tr-crm-ledger__chip-row">
                                        @if (! empty($row['source_label']))
                                            <span class="tr-crm-ledger__chip tr-crm-ledger__chip--source">{{ $row['source_label'] }}</span>
                                        @endif
                                        @if (! empty($row['spa_branch_name']))
                                            <span class="tr-crm-ledger__chip tr-crm-ledger__chip--branch">{{ $row['spa_branch_name'] }}</span>
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="tr-crm-ledger__cell tr-crm-ledger__cell--specialist">
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
                        <td class="tr-crm-ledger__cell tr-crm-ledger__cell--amount">
                            <span class="tr-crm-ledger__amount-value">{{ $row['total_formatted'] ?? '—' }}</span>
                            @if (! empty($row['id']))
                                <span class="tr-crm-ledger__ref">B{{ $row['id'] }}</span>
                            @endif
                        </td>
                        <td class="tr-crm-ledger__cell tr-crm-ledger__cell--status">
                            <div class="tr-crm-ledger__status-wrap">
                                <span
                                    class="tr-crm-ledger__status-pill tr-crm-ledger__status-pill--{{ $row['status'] ?? 'pending' }}"
                                >
                                    {{ $row['status_label'] ?? '—' }}
                                </span>
                                @if (! empty($row['inline_alerts']))
                                    <span class="tr-crm-ledger__chip-row tr-crm-ledger__chip-row--alerts">
                                        @foreach (array_slice($row['inline_alerts'], 0, 2) as $alert)
                                            <span class="tr-crm-ledger__chip tr-crm-ledger__chip--{{ $alert['level'] ?? 'info' }}">
                                                {{ $alert['label'] ?? '' }}
                                            </span>
                                        @endforeach
                                    </span>
                                @endif
                                <span class="tr-crm-ledger__open-hint" aria-hidden="true">
                                    <i class="fa fa-chevron-right"></i>
                                </span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="tr-crm-ledger__empty">
                            <i class="fa fa-inbox" aria-hidden="true"></i>
                            <span>{{ TrLang::trans('admin.crm.ledger_empty') }}</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
