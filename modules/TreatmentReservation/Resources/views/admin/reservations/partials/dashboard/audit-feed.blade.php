@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
@endphp

@php
    $recentActivity = $recentActivity ?? [];
@endphp

<section class="tr-crm-panel tr-crm-panel--audit">
    <header class="tr-crm-panel__head">
        <div>
            <h3 class="tr-crm-panel__title">{{ TrLang::trans('admin.crm.audit_title') }}</h3>
            <p class="tr-crm-panel__lead">{{ TrLang::trans('admin.crm.audit_lead') }}</p>
        </div>
        <span class="tr-crm-panel__badge">{{ TrLang::trans('admin.crm.badge_feed') }}</span>
    </header>

    <ul class="tr-crm-audit" data-crm-list>
        @forelse ($recentActivity as $activity)
            <li
                class="tr-crm-audit__item"
                data-search="{{ strtolower(($activity['actor_name'] ?? '') . ' ' . ($activity['summary'] ?? '') . ' ' . ($activity['customer_name'] ?? '')) }}"
            >
                <span class="tr-crm-audit__dot" aria-hidden="true"></span>
                <div class="tr-crm-audit__body">
                    <p>
                        <strong>{{ $activity['actor_name'] ?? '—' }}</strong>
                        {{ $activity['summary'] ?? '' }}
                    </p>
                    <span>{{ $activity['customer_name'] ?? '' }} · {{ $activity['treatment_name'] ?? '' }}</span>
                </div>
                <span class="tr-crm-audit__time">{{ $activity['created_at'] ?? '' }}</span>
            </li>
        @empty
            <li class="tr-crm-empty tr-crm-empty--inline">
                <i class="fa fa-history"></i>
                <p>{{ TrLang::trans('admin.crm.no_activity') }}</p>
            </li>
        @endforelse
    </ul>
</section>
