@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
@endphp

@php
    $beauticians = $beauticians ?? [];
    $filterDateValue = $filterDateValue ?? today()->toDateString();
    $filterDateLabel = $filterDateLabel ?? TrLang::trans('admin.crm.date_today');
    $dateFilter = $dateFilter ?? 'today';
    $sessionsLabel = $dateFilter === 'today'
        ? 'admin.crm.specialist_sessions'
        : 'admin.crm.specialist_sessions_date';
    $statusLabels = [
        'with_client' => TrLang::trans('admin.crm.specialist_with_client'),
        'scheduled' => TrLang::trans('admin.crm.specialist_scheduled'),
        'available' => TrLang::trans('admin.crm.specialist_available'),
        'unavailable' => TrLang::trans('admin.crm.specialist_unavailable'),
    ];
@endphp

<section class="tr-crm-panel tr-crm-panel--specialists">
    <header class="tr-crm-panel__head">
        <div>
            <h3 class="tr-crm-panel__title">{{ TrLang::trans('admin.crm.specialists_title') }}</h3>
            <p class="tr-crm-panel__lead">{{ TrLang::trans('admin.crm.specialists_lead') }}</p>
        </div>
        <span class="tr-crm-specialists__count">{{ TrLang::trans('admin.crm.specialists_count', ['count' => count($beauticians)]) }}</span>
    </header>

    <ul class="tr-crm-specialists" data-crm-list>
        @forelse ($beauticians as $beautician)
            @php
                $status = $beautician['status'] ?? 'available';
                $availableToday = (bool) ($beautician['available_today'] ?? true);
            @endphp
            <li
                class="tr-crm-specialist"
                data-beautician-id="{{ $beautician['id'] ?? '' }}"
                data-specialist-status="{{ $status }}"
                data-search="{{ strtolower(($beautician['name'] ?? '') . ' ' . ($beautician['job_title'] ?? '')) }}"
            >
                @if (! empty($beautician['avatar']))
                    <span
                        class="tr-crm-specialist__avatar tr-crm-specialist__avatar--photo"
                        style="background-color: {{ $beautician['color'] ?? '#6d2847' }}"
                    >
                        <img
                            src="{{ $beautician['avatar'] }}"
                            alt="{{ $beautician['name'] ?? '' }}"
                            draggable="false"
                        >
                    </span>
                @else
                    <span
                        class="tr-crm-specialist__avatar"
                        style="background-color: {{ $beautician['color'] ?? '#6d2847' }}"
                    >{{ $beautician['initial'] ?? '?' }}</span>
                @endif
                <div class="tr-crm-specialist__body">
                    @if (! empty($crmSpecialistProfileUrl))
                        <a
                            href="{{ $crmSpecialistProfileUrl }}"
                            class="tr-crm-specialist__name-link"
                            title="{{ TrLang::trans('admin.crm.specialist_profile_link_title', ['name' => $beautician['name'] ?? '']) }}"
                        >
                            {{ $beautician['name'] ?? '—' }}
                        </a>
                    @elseif (auth()->user()?->hasAccess('admin.beauticians.edit'))
                        <a
                            href="{{ route('admin.beauticians.portal.availability', $beautician['id']) }}"
                            class="tr-crm-specialist__name-link"
                            title="{{ TrLang::trans('admin.crm.specialist_profile_link_title', ['name' => $beautician['name'] ?? '']) }}"
                        >
                            {{ $beautician['name'] ?? '—' }}
                        </a>
                    @else
                        <strong>{{ $beautician['name'] ?? '—' }}</strong>
                    @endif
                    @if (! empty($beautician['job_title']))
                        <span>{{ $beautician['job_title'] }}</span>
                    @endif
                    <span class="tr-crm-specialist__sessions">
                        {{ TrLang::trans($sessionsLabel, ['count' => $beautician['session_count'] ?? 0, 'date' => $filterDateLabel]) }}
                    </span>
                </div>
                <div class="tr-crm-specialist__controls">
                    @if (! empty($crmShowSpecialistToggle) || auth()->user()?->hasAccess('admin.treatment_reservations.edit'))
                        <label class="tr-crm-specialist__toggle" title="{{ TrLang::trans('admin.crm.specialist_toggle_aria') }}">
                            <input
                                type="checkbox"
                                class="tr-crm-specialist__toggle-input"
                                data-specialist-toggle
                                data-beautician-id="{{ $beautician['id'] ?? '' }}"
                                data-toggle-date="{{ $filterDateValue }}"
                                @checked($availableToday)
                                aria-label="{{ TrLang::trans('admin.crm.specialist_toggle_aria', ['name' => $beautician['name'] ?? '']) }}"
                            >
                            <span class="tr-crm-specialist__toggle-ui" aria-hidden="true"></span>
                        </label>
                    @endif
                    <span
                        class="tr-crm-specialist__badge tr-crm-specialist__badge--{{ $status }}"
                        data-specialist-badge
                    >
                        {{ $statusLabels[$status] ?? $status }}
                    </span>
                </div>
            </li>
        @empty
            <li class="tr-crm-empty tr-crm-empty--inline">
                <i class="fa fa-user-o"></i>
                <p>{{ TrLang::trans('admin.crm.no_beauticians_today') }}</p>
            </li>
        @endforelse
    </ul>
</section>
