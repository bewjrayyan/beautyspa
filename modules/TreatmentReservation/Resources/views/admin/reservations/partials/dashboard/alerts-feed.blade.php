@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
@endphp

@php
    $alerts = $alerts ?? [];
@endphp

<section class="tr-crm-panel tr-crm-panel--alerts-feed">
    <header class="tr-crm-panel__head">
        <div>
            <h3 class="tr-crm-panel__title">{{ TrLang::trans('admin.crm.alerts_title') }}</h3>
            <p class="tr-crm-panel__lead">{{ TrLang::trans('admin.crm.alerts_lead') }}</p>
        </div>
        <span class="tr-crm-panel__badge tr-crm-panel__badge--live">{{ TrLang::trans('admin.crm.badge_live') }}</span>
    </header>

    <ul class="tr-crm-alerts" data-crm-list>
        @forelse ($alerts as $item)
            <li
                class="tr-crm-alert tr-crm-alert--{{ $item['urgency'] ?? 'info' }}"
                data-search="{{ strtolower(($item['customer_name'] ?? '') . ' ' . ($item['message'] ?? '')) }}"
            >
                <span class="tr-crm-alert__dot" aria-hidden="true"></span>
                <div class="tr-crm-alert__body">
                    <strong>{{ $item['customer_name'] ?? '—' }}</strong>
                    <p>{{ $item['message'] ?? '' }}</p>
                </div>
                <span class="tr-crm-alert__when">{{ $item['time_display'] ?? '' }}</span>
            </li>
        @empty
            <li class="tr-crm-empty tr-crm-empty--inline">
                <i class="fa fa-check-circle"></i>
                <p>{{ TrLang::trans('admin.crm.no_alerts') }}</p>
            </li>
        @endforelse
    </ul>
</section>
