@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;

    $urgencyAsModal = $urgencyAlertsAsModal ?? (
        request()->routeIs('admin.dashboard.index')
        || request()->routeIs('admin.beauticians.portal*')
        || request()->routeIs('admin.treatment_reservations.portal')
        || request()->routeIs('admin.treatment_reservations.portal.account')
        || request()->routeIs('admin.treatment_reservations.portal.availability')
    );
@endphp

@if (! empty($jobUrgencyAlerts['has_alerts']))
    @if ($urgencyAsModal)
        @push('admin_modals')
            <div class="tr-urgency-modal" id="tr-urgency-modal" role="presentation" hidden>
                <div class="tr-urgency-modal__backdrop" data-dismiss-urgency aria-hidden="true"></div>
                <div
                    class="tr-urgency-modal__dialog"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="tr-urgency-modal-title"
                >
    @endif

    <div
        class="tr-urgency-alerts tr-urgency-alerts--{{ $jobUrgencyAlerts['highest_urgency'] }}{{ $urgencyAsModal ? ' tr-urgency-alerts--modal' : ' tr-urgency-alerts--inline' }}"
        id="{{ $urgencyAsModal ? null : 'tr-urgency-alerts' }}"
        role="alert"
        aria-live="polite"
        @unless ($urgencyAsModal) hidden @endunless
    >
        <div class="tr-urgency-alerts__header">
            <div class="tr-urgency-alerts__icon" aria-hidden="true">
                <i class="fa fa-bell"></i>
            </div>
            <div class="tr-urgency-alerts__intro">
                <h4 class="tr-urgency-alerts__title" id="tr-urgency-modal-title">{{ $jobUrgencyAlerts['headline'] }}</h4>
                <p class="tr-urgency-alerts__lead">{{ $jobUrgencyAlerts['lead'] }}</p>
                <div class="tr-urgency-alerts__counts">
                    @if ($jobUrgencyAlerts['critical_count'] > 0)
                        <span class="tr-urgency-alerts__count tr-urgency-alerts__count--critical">
                            {{ TrLang::trans('admin.urgency.count_critical', ['count' => $jobUrgencyAlerts['critical_count']]) }}
                        </span>
                    @endif
                    @if ($jobUrgencyAlerts['warning_count'] > 0)
                        <span class="tr-urgency-alerts__count tr-urgency-alerts__count--warning">
                            {{ TrLang::trans('admin.urgency.count_warning', ['count' => $jobUrgencyAlerts['warning_count']]) }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="tr-urgency-alerts__actions">
                @if (! $urgencyAsModal && ! empty($jobUrgencyAlerts['action_url']))
                    <a href="{{ $jobUrgencyAlerts['action_url'] }}" class="btn btn-sm btn-default tr-urgency-alerts__action-btn">
                        <i class="fa fa-columns" aria-hidden="true"></i>
                        <span>{{ $jobUrgencyAlerts['action_label'] }}</span>
                    </a>
                @endif
                <button type="button" class="tr-urgency-alerts__dismiss" data-dismiss-urgency aria-label="{{ trans('admin::admin.close') }}">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <ul class="tr-urgency-alerts__list">
            @foreach ($jobUrgencyAlerts['items'] as $item)
                <li class="tr-urgency-alerts__item tr-urgency-alerts__item--{{ $item['urgency'] }}">
                    @if (! empty($item['order_url']))
                        <a href="{{ $item['order_url'] }}" class="tr-urgency-alerts__item-hit">
                    @else
                        <div class="tr-urgency-alerts__item-hit tr-urgency-alerts__item-hit--static">
                    @endif
                        <div class="tr-urgency-alerts__item-time">
                            <span class="tr-urgency-alerts__badge">{{ $item['urgency_label'] }}</span>
                            <strong class="tr-urgency-alerts__time">{{ $item['time_display'] }}</strong>
                            <span class="tr-urgency-alerts__date">{{ $item['date_display'] }}</span>
                        </div>
                        <div class="tr-urgency-alerts__item-main">
                            <strong class="tr-urgency-alerts__item-title">{{ $item['customer_name'] }}</strong>
                            <span class="tr-urgency-alerts__item-meta">
                                {{ $item['treatment_name'] }}
                                @if (! empty($item['show_beautician']) && ! empty($item['beautician_name']))
                                    · {{ $item['beautician_name'] }}
                                @endif
                            </span>
                            <span class="tr-urgency-alerts__item-message">{{ $item['message'] }}</span>
                            <span class="tr-urgency-alerts__item-status">{{ $item['status_label'] }}</span>
                        </div>
                        @if (! empty($item['order_url']))
                            <span class="tr-urgency-alerts__item-chevron" aria-hidden="true">
                                <i class="fa fa-chevron-right"></i>
                            </span>
                        @endif
                    @if (! empty($item['order_url']))
                        </a>
                    @else
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($urgencyAsModal)
            <div class="tr-urgency-alerts__footer">
                @if (! empty($jobUrgencyAlerts['action_url']))
                    <a
                        href="{{ $jobUrgencyAlerts['action_url'] }}"
                        class="btn btn-primary"
                        data-urgency-action
                    >
                        <i class="fa fa-columns" aria-hidden="true"></i>
                        {{ $jobUrgencyAlerts['action_label'] }}
                    </a>
                @endif
                <button type="button" class="btn btn-default" data-dismiss-urgency>
                    {{ TrLang::trans('admin.urgency.modal_dismiss') }}
                </button>
            </div>
        @endif
    </div>

    @if ($urgencyAsModal)
                </div>
            </div>
        @endpush
    @endif

    @push('scripts')
        <script>
            (function () {
                var DISMISS_KEY = 'tr_urgency_dismissed_session';
                var modal = document.getElementById('tr-urgency-modal');
                var inline = document.getElementById('tr-urgency-alerts');

                function isDismissed() {
                    try {
                        return sessionStorage.getItem(DISMISS_KEY) === '1';
                    } catch (e) {
                        return false;
                    }
                }

                function markDismissed() {
                    try {
                        sessionStorage.setItem(DISMISS_KEY, '1');
                    } catch (e) {}
                }

                function hideRoot(root) {
                    if (!root) {
                        return;
                    }

                    root.hidden = true;
                    root.classList.add('tr-urgency-modal--hidden');
                    root.setAttribute('aria-hidden', 'true');
                }

                function showRoot(root) {
                    if (!root) {
                        return;
                    }

                    root.hidden = false;
                    root.classList.remove('tr-urgency-modal--hidden');
                    root.removeAttribute('aria-hidden');
                }

                function closeUrgency() {
                    markDismissed();
                    document.body.classList.remove('tr-urgency-modal-open');

                    if (modal) {
                        hideRoot(modal);
                        return;
                    }

                    if (inline) {
                        hideRoot(inline);
                        return;
                    }

                    document.querySelectorAll('.tr-urgency-alerts').forEach(function (el) {
                        hideRoot(el);
                    });
                }

                if (isDismissed()) {
                    hideRoot(modal);
                    hideRoot(inline);
                    return;
                }

                if (modal) {
                    showRoot(modal);
                    document.body.classList.add('tr-urgency-modal-open');

                    modal.querySelectorAll('[data-dismiss-urgency]').forEach(function (button) {
                        button.addEventListener('click', closeUrgency);
                    });

                    modal.querySelectorAll('[data-urgency-action]').forEach(function (link) {
                        link.addEventListener('click', function () {
                            document.body.classList.remove('tr-urgency-modal-open');
                        });
                    });

                    document.addEventListener('keydown', function onEscape(event) {
                        if (event.key !== 'Escape' || modal.hidden) {
                            return;
                        }

                        closeUrgency();
                    });

                    return;
                }

                if (inline) {
                    showRoot(inline);
                }

                document.querySelectorAll('[data-dismiss-urgency]').forEach(function (button) {
                    button.addEventListener('click', closeUrgency);
                });
            })();
        </script>
    @endpush
@endif
