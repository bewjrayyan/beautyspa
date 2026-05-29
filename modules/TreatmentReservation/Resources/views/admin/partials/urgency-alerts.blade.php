@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;

    $urgencyAsModal = $urgencyAlertsAsModal ?? (
        request()->routeIs('admin.dashboard.index')
        || request()->routeIs('admin.treatment_reservations.portal')
        || request()->routeIs('admin.treatment_reservations.portal.account')
        || request()->routeIs('admin.treatment_reservations.portal.availability')
    );
@endphp

@if (! empty($jobUrgencyAlerts['has_alerts']))
    @if ($urgencyAsModal)
        @push('admin_modals')
            <div class="tr-urgency-modal" id="tr-urgency-modal" role="presentation">
                <div class="tr-urgency-modal__backdrop" data-dismiss-urgency aria-hidden="true"></div>
                <div
                    class="tr-urgency-modal__dialog"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="tr-urgency-modal-title"
                >
    @endif

    <div
        class="tr-urgency-alerts tr-urgency-alerts--{{ $jobUrgencyAlerts['highest_urgency'] }}{{ $urgencyAsModal ? ' tr-urgency-alerts--modal' : '' }}"
        role="alert"
        aria-live="polite"
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
                    <a href="{{ $jobUrgencyAlerts['action_url'] }}" class="btn btn-sm btn-default">
                        <i class="fa fa-columns" aria-hidden="true"></i>
                        {{ $jobUrgencyAlerts['action_label'] }}
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
                    <span class="tr-urgency-alerts__badge">{{ $item['urgency_label'] }}</span>
                    <div class="tr-urgency-alerts__item-main">
                        <strong class="tr-urgency-alerts__item-title">
                            {{ $item['time_display'] }} · {{ $item['date_display'] }}
                            — {{ $item['customer_name'] }}
                        </strong>
                        <span class="tr-urgency-alerts__item-meta">
                            {{ $item['treatment_name'] }}
                            @if (! empty($item['show_beautician']) && ! empty($item['beautician_name']))
                                <br>
                                <span class="tr-urgency-alerts__item-beautician">{{ $item['beautician_name'] }}</span>
                            @endif
                            · {{ $item['status_label'] }}
                        </span>
                        <span class="tr-urgency-alerts__item-message">{{ $item['message'] }}</span>
                    </div>
                    @if (! empty($item['order_url']))
                        <a href="{{ $item['order_url'] }}" class="btn btn-xs btn-default tr-urgency-alerts__item-link">
                            {{ TrLang::trans('admin.kanban.view_order') }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($urgencyAsModal)
            <div class="tr-urgency-alerts__footer">
                @if (! empty($jobUrgencyAlerts['action_url']))
                    <a href="{{ $jobUrgencyAlerts['action_url'] }}" class="btn btn-primary">
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
                const modal = document.getElementById('tr-urgency-modal');

                function closeUrgencyModal() {
                    if (modal) {
                        modal.classList.add('tr-urgency-modal--hidden');
                        document.body.classList.remove('tr-urgency-modal-open');
                        return;
                    }

                    document.querySelectorAll('[data-dismiss-urgency]').forEach((trigger) => {
                        trigger.closest('.tr-urgency-alerts')?.remove();
                    });
                }

                if (modal) {
                    document.body.classList.add('tr-urgency-modal-open');

                    modal.querySelectorAll('[data-dismiss-urgency]').forEach((button) => {
                        button.addEventListener('click', closeUrgencyModal);
                    });

                    document.addEventListener('keydown', function onEscape(event) {
                        if (event.key !== 'Escape' || modal.classList.contains('tr-urgency-modal--hidden')) {
                            return;
                        }

                        closeUrgencyModal();
                    });

                    return;
                }

                document.querySelectorAll('[data-dismiss-urgency]').forEach((button) => {
                    button.addEventListener('click', closeUrgencyModal);
                });
            })();
        </script>
    @endpush
@endif
