<div class="account-booking-overview" role="status" aria-atomic="true">
    <div class="account-booking-overview__metric">
        <span class="account-booking-overview__icon" aria-hidden="true"><i class="las la-receipt"></i></span>
        <span>
            <strong>{{ $bookingOrderCount }}</strong>
            {{ trans_choice('treatmentreservation::public.booking_order_count', $bookingOrderCount, ['count' => $bookingOrderCount]) }}
        </span>
    </div>
    <div class="account-booking-overview__metric">
        <span class="account-booking-overview__icon" aria-hidden="true"><i class="las la-calendar-check"></i></span>
        <span>
            <strong>{{ $appointmentCount }}</strong>
            {{ trans_choice('treatmentreservation::public.appointment_count', $appointmentCount, ['count' => $appointmentCount]) }}
        </span>
    </div>
    <p>{{ trans('treatmentreservation::public.manage_individually_hint') }}</p>
</div>

<div class="account-booking-groups">
    @foreach ($bookingGroups as $group)
        @php
            $order = $group['order'];
            $appointments = $group['appointments'];
            $groupReference = $order?->id
                ? trans('treatmentreservation::public.booking_order_reference', ['id' => $order->id])
                : trans('treatmentreservation::public.direct_booking_reference', ['id' => $appointments->first()->id]);
            $groupDomId = $order?->id ? 'order-' . $order->id : 'booking-' . $appointments->first()->id;
            $paymentStatus = str_replace('_', '-', (string) $group['payment_status']);
        @endphp

        <section class="account-booking-group" aria-labelledby="booking-group-{{ $groupDomId }}">
            <header class="account-booking-group__header">
                <div class="account-booking-group__identity">
                    <span class="account-booking-group__eyebrow">
                        {{ $order
                            ? trans('treatmentreservation::public.booking_order')
                            : trans('treatmentreservation::public.direct_booking') }}
                    </span>
                    <h2 id="booking-group-{{ $groupDomId }}">{{ $groupReference }}</h2>
                    @if ($group['created_at'])
                        <span class="account-booking-group__created">
                            <i class="las la-clock" aria-hidden="true"></i>
                            {{ trans('treatmentreservation::public.booked_on', ['date' => $group['created_at']->translatedFormat('d M Y')]) }}
                        </span>
                    @endif
                </div>

                <div class="account-booking-group__summary">
                    <span class="account-booking-group__count">
                        <i class="las la-layer-group" aria-hidden="true"></i>
                        {{ trans_choice('treatmentreservation::public.appointment_count_with_number', $group['appointment_count'], [
                            'count' => $group['appointment_count'],
                        ]) }}
                    </span>
                    <span class="account-booking-group__payment account-booking-group__payment--{{ $paymentStatus }}">
                        <span>{{ trans('treatmentreservation::public.payment') }}</span>
                        <strong>{{ $group['payment_label'] }}</strong>
                    </span>
                </div>
            </header>

            <div class="account-booking-group__progress">
                <span>{{ trans('treatmentreservation::public.schedule_progress', [
                    'scheduled' => $group['scheduled_count'],
                    'total' => $group['appointment_count'],
                ]) }}</span>
                @if ($group['scheduled_count'] < $group['appointment_count'])
                    <span class="account-booking-group__pending">{{ trans('treatmentreservation::public.schedule_pending') }}</span>
                @endif
            </div>

            <ol class="account-booking-group__appointments">
                @foreach ($appointments as $booking)
                    @php
                        $statusKey = 'treatmentreservation::public.statuses.' . $booking->status;
                        $statusLabel = trans()->has($statusKey)
                            ? trans($statusKey)
                            : ucfirst(str_replace('_', ' ', $booking->status));
                        $treatmentLine = $booking->treatmentLineMeta();
                        $isTba = $booking->isTbaSchedule();
                        $location = $booking->spaBranchLabel();
                    @endphp

                    <li>
                        <article class="account-appointment-card" data-booking-id="{{ $booking->id }}">
                            <div class="account-appointment-card__number" aria-hidden="true">{{ $loop->iteration }}</div>

                            <div class="account-appointment-card__content">
                                <div class="account-appointment-card__top">
                                    <div>
                                        <span class="account-appointment-card__label">
                                            {{ trans('treatmentreservation::public.appointment_number', ['number' => $loop->iteration]) }}
                                        </span>
                                        <span
                                            class="account-appointment-card__ref"
                                            title="{{ trans('treatmentreservation::public.appointment_reference_hint') }}"
                                        >{{ trans('treatmentreservation::public.appointment_reference', ['code' => $booking->referenceCode()]) }}</span>
                                        <h3 class="account-appointment-card__title">{{ $treatmentLine['product_name'] }}</h3>
                                        @if ($treatmentLine['treatment_selection'])
                                            <p class="account-appointment-card__selection">{{ $treatmentLine['treatment_selection'] }}</p>
                                        @endif
                                    </div>
                                    <span class="account-appointment-card__status account-appointment-card__status--{{ $booking->status }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <dl class="account-appointment-card__details">
                                    <div>
                                        <dt><i class="las la-calendar" aria-hidden="true"></i>{{ trans('treatmentreservation::public.date') }}</dt>
                                        <dd>{{ $isTba ? trans('treatmentreservation::public.to_be_scheduled') : ($booking->appointment_date?->translatedFormat('d M Y') ?? '—') }}</dd>
                                    </div>
                                    <div>
                                        <dt><i class="las la-clock" aria-hidden="true"></i>{{ trans('treatmentreservation::public.time') }}</dt>
                                        <dd>{{ $isTba ? trans('treatmentreservation::public.to_be_scheduled') : ($booking->displayAppointmentTime() ?: '—') }}</dd>
                                    </div>
                                    <div>
                                        <dt><i class="las la-user" aria-hidden="true"></i>{{ trans('treatmentreservation::public.beautician') }}</dt>
                                        <dd>{{ $booking->beautician?->name ?? '—' }}</dd>
                                    </div>
                                    @if ($location)
                                        <div>
                                            <dt><i class="las la-map-marker" aria-hidden="true"></i>{{ trans('treatmentreservation::public.location') }}</dt>
                                            <dd>{{ $location }}</dd>
                                        </div>
                                    @endif
                                </dl>

                                <div class="account-appointment-card__beautician-note{{ filled($booking->beautician_notes) ? '' : ' is-empty' }}">
                                    <span class="account-appointment-card__beautician-note-icon" aria-hidden="true">
                                        <i class="las la-comment-medical"></i>
                                    </span>
                                    <div>
                                        <span class="account-appointment-card__beautician-note-label">
                                            {{ trans('treatmentreservation::public.beautician_notes') }}
                                        </span>
                                        <p>{{ filled($booking->beautician_notes)
                                            ? $booking->beautician_notes
                                            : trans('treatmentreservation::public.beautician_notes_empty') }}</p>
                                        @if ($booking->beautician_notes_at)
                                            <span class="account-appointment-card__beautician-note-time">
                                                <i class="las la-clock" aria-hidden="true"></i>
                                                {{ trans('treatmentreservation::public.beautician_notes_recorded_at', [
                                                    'date' => $booking->beautician_notes_at->translatedFormat('d M Y'),
                                                    'time' => $booking->beautician_notes_at->format('g:i A'),
                                                ]) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="account-appointment-card__actions">
                                    <button
                                        type="button"
                                        class="btn btn-default btn-sm js-reschedule-toggle"
                                        aria-expanded="false"
                                        aria-controls="reschedule-form-{{ $booking->id }}"
                                    >
                                        <i class="las la-calendar-alt" aria-hidden="true"></i>
                                        {{ $isTba
                                            ? trans('treatmentreservation::public.schedule_appointment')
                                            : trans('treatmentreservation::public.reschedule') }}
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm js-cancel-booking">
                                        <i class="las la-times-circle" aria-hidden="true"></i>
                                        {{ trans('treatmentreservation::public.cancel') }}
                                    </button>
                                </div>

                                @include('treatmentreservation::public.booking.partials.reschedule_form', ['booking' => $booking])
                            </div>
                        </article>
                    </li>
                @endforeach
            </ol>
        </section>
    @endforeach
</div>
