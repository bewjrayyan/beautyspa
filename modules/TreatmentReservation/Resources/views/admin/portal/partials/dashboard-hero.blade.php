@php
    $profileColor = $beautician->profile_color ?? '#6366f1';
    $todayCount = $todayAppointments->count();
    $showPortalActions = empty($adminPortalPreview);
@endphp

<header class="tr-portal-dashboard-hero" style="--tr-portal-accent: {{ $profileColor }};">
    <div class="tr-portal-dashboard-hero__main">
        <div class="tr-portal-dashboard-hero__copy">
            <span class="tr-portal-dashboard-hero__eyebrow">
                <i class="fa fa-magic" aria-hidden="true"></i>
                {{ trans('treatmentreservation::admin.portal.dashboard_welcome') }}
            </span>
            <h1 class="tr-portal-dashboard-hero__title">
                {{ trans('treatmentreservation::admin.portal.welcome', ['name' => $beautician->name]) }}
            </h1>
            <p class="tr-portal-dashboard-hero__lead">
                {{ trans('treatmentreservation::admin.portal.dashboard_lead') }}
            </p>

            <div class="tr-portal-dashboard-hero__meta">
                <span class="tr-portal-dashboard-hero__chip">
                    <i class="fa fa-calendar-o" aria-hidden="true"></i>
                    {{ trans('treatmentreservation::admin.portal.dashboard_today_chip', ['date' => now()->format('d M Y')]) }}
                </span>

                @if ($beautician->job_title)
                    <span class="tr-portal-dashboard-hero__chip tr-portal-dashboard-hero__chip--role">
                        <i class="fa fa-briefcase" aria-hidden="true"></i>
                        {{ $beautician->job_title }}
                    </span>
                @endif
            </div>
        </div>

        <div class="tr-portal-dashboard-hero__profile">
            @include('treatmentreservation::admin.portal.partials.avatar', [
                'beautician' => $beautician,
                'user' => $beautician->user,
                'class' => ' tr-portal-avatar--lg tr-portal-avatar--hero',
            ])
        </div>
    </div>

    <div class="tr-portal-dashboard-hero__metrics" role="list">
        <article class="tr-portal-dashboard-hero__metric tr-portal-dashboard-hero__metric--today" role="listitem">
            <span class="tr-portal-dashboard-hero__metric-value">{{ number_format($todayCount) }}</span>
            <span class="tr-portal-dashboard-hero__metric-label">{{ trans('treatmentreservation::admin.portal.dashboard_metric_today') }}</span>
        </article>

        <article class="tr-portal-dashboard-hero__metric tr-portal-dashboard-hero__metric--pending" role="listitem">
            <span class="tr-portal-dashboard-hero__metric-value">{{ number_format($stats['pending']) }}</span>
            <span class="tr-portal-dashboard-hero__metric-label">{{ trans('treatmentreservation::admin.kanban.pending') }}</span>
        </article>

        <article class="tr-portal-dashboard-hero__metric tr-portal-dashboard-hero__metric--progress" role="listitem">
            <span class="tr-portal-dashboard-hero__metric-value">{{ number_format($stats['inProgress']) }}</span>
            <span class="tr-portal-dashboard-hero__metric-label">{{ trans('treatmentreservation::admin.kanban.in_progress') }}</span>
        </article>

        <article class="tr-portal-dashboard-hero__metric tr-portal-dashboard-hero__metric--completed" role="listitem">
            <span class="tr-portal-dashboard-hero__metric-value">{{ number_format($performanceStats['weekCompleted']) }}</span>
            <span class="tr-portal-dashboard-hero__metric-label">{{ trans('treatmentreservation::admin.portal.dashboard_metric_week') }}</span>
        </article>
    </div>

    <div class="tr-portal-dashboard-hero__actions">
        @if ($showPortalActions)
            @hasAccess('admin.treatment_reservations.portal.create')
                <button
                    type="button"
                    class="tr-portal-dashboard-hero__action tr-portal-dashboard-hero__action--primary"
                    data-toggle="modal"
                    data-target="#tr-portal-manual-booking-modal"
                >
                    <i class="fa fa-plus" aria-hidden="true"></i>
                    {{ trans('treatmentreservation::admin.manual_booking.portal_open') }}
                </button>
            @endHasAccess
            <a href="{{ route('admin.treatment_reservations.portal.account') }}" class="tr-portal-dashboard-hero__action">
                <i class="fa fa-user" aria-hidden="true"></i>
                {{ trans('treatmentreservation::admin.portal.account_title') }}
            </a>
            <a href="{{ route('admin.treatment_reservations.portal.availability') }}" class="tr-portal-dashboard-hero__action">
                <i class="fa fa-clock-o" aria-hidden="true"></i>
                {{ trans('treatmentreservation::admin.availability.title') }}
            </a>
        @elseif (! empty($backUrl))
            <a href="{{ $backUrl }}" class="tr-portal-dashboard-hero__action tr-portal-dashboard-hero__action--muted">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                {{ trans('beautician::beauticians.form.back_to_beautician_profile') }}
            </a>
        @endif
    </div>
</header>
