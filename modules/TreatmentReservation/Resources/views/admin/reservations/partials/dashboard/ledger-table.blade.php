@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
@endphp

@php
    $ledger = $ledger ?? [];
    $ledgerCount = $ledgerCount ?? count($ledger);
@endphp

<section class="tr-crm-panel tr-crm-panel--ledger">
    <header class="tr-crm-panel__head tr-crm-panel__head--compact">
        <div>
            <h3 class="tr-crm-panel__title">
                {{ TrLang::trans('admin.crm.ledger_title') }}
                <span class="tr-crm-panel__count">{{ $ledgerCount }}</span>
            </h3>
        </div>
    </header>

    <div class="tr-crm-ledger-wrap">
        <table class="tr-crm-ledger tr-crm-ledger--compact">
            <thead>
                <tr>
                    <th>{{ TrLang::trans('admin.crm.ledger_client') }}</th>
                    <th>{{ TrLang::trans('admin.crm.ledger_appointment') }}</th>
                    <th>{{ TrLang::trans('admin.crm.ledger_specialist') }}</th>
                    <th class="tr-crm-ledger__summary">{{ TrLang::trans('admin.crm.ledger_summary') }}</th>
                </tr>
            </thead>
            <tbody data-crm-list>
                @forelse ($ledger as $row)
                    <tr
                        class="tr-crm-ledger__row tr-crm-ledger__row--clickable"
                        data-booking-id="{{ $row['id'] ?? '' }}"
                        data-search="{{ strtolower(($row['customer_name'] ?? '') . ' ' . ($row['customer_phone'] ?? '') . ' ' . ($row['customer_email'] ?? '') . ' ' . ($row['treatment_name'] ?? '') . ' ' . ($row['appointment_date_short'] ?? $row['appointment_date'] ?? '') . ' ' . ($row['appointment_time'] ?? '') . ' ' . ($row['beautician_name'] ?? '') . ' ' . ($row['status_label'] ?? '') . ' ' . ($row['total_formatted'] ?? '') . ' ' . ($row['id'] ?? '')) }}"
                        role="button"
                        tabindex="0"
                        aria-label="{{ ($row['customer_name'] ?? TrLang::trans('admin.crm.ledger_unknown_client')) . ', ' . ($row['treatment_name'] ?? TrLang::trans('admin.crm.ledger_unknown_treatment')) }}"
                    >
                        <td class="tr-crm-ledger__cell tr-crm-ledger__cell--client">
                            <button
                                type="button"
                                class="tr-crm-ledger__customer-link"
                                data-customer-profile
                                data-booking-id="{{ $row['id'] ?? '' }}"
                                onclick="event.stopPropagation()"
                            >{{ $row['customer_name'] ?? TrLang::trans('admin.crm.ledger_unknown_client') }}</button>
                        </td>
                        <td class="tr-crm-ledger__cell tr-crm-ledger__cell--appointment">
                            <span class="tr-crm-ledger__treatment-name">{{ $row['treatment_name'] ?? TrLang::trans('admin.crm.ledger_unknown_treatment') }}</span>
                            <span class="tr-crm-ledger__schedule">
                                {{ $row['appointment_date_short'] ?? $row['appointment_date'] ?? TrLang::trans('admin.crm.ledger_unscheduled') }}
                                <span class="tr-crm-ledger__schedule-sep">·</span>
                                {{ $row['appointment_time'] ?? TrLang::trans('admin.crm.ledger_time_tbc') }}
                            </span>
                        </td>
                        <td class="tr-crm-ledger__cell tr-crm-ledger__cell--specialist">
                            <span class="tr-crm-ledger__specialist{{ empty($row['beautician_assigned']) ? ' tr-crm-ledger__specialist--unassigned' : '' }}">
                                <span
                                    class="tr-crm-ledger__avatar tr-crm-ledger__avatar--xs"
                                    style="background-color: {{ $row['beautician_color'] ?? '#6d2847' }}"
                                >{{ $row['beautician_initial'] ?? '?' }}</span>
                                <span class="tr-crm-ledger__specialist-name">{{ $row['beautician_name'] ?? TrLang::trans('admin.crm.ledger_unassigned') }}</span>
                            </span>
                        </td>
                        <td class="tr-crm-ledger__cell tr-crm-ledger__cell--summary">
                            <div class="tr-crm-ledger__summary-wrap">
                                <span class="tr-crm-ledger__amount-value">{{ $row['total_formatted'] ?? '—' }}</span>
                                <span class="tr-crm-ledger__status-pill tr-crm-ledger__status-pill--{{ $row['status'] ?? 'pending' }}">
                                    {{ $row['status_label'] ?? '—' }}
                                </span>
                                <span class="tr-crm-ledger__open-hint" aria-hidden="true">
                                    <i class="fa fa-chevron-right"></i>
                                </span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="tr-crm-ledger__empty">
                            <i class="fa fa-inbox" aria-hidden="true"></i>
                            <span>{{ TrLang::trans('admin.crm.ledger_empty') }}</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
