<form
    id="reschedule-form-{{ $booking->id }}"
    class="account-appointment-card__reschedule hide js-reschedule-form"
    data-slots-url="{{ route('treatment_reservations.booking.slots', ['id' => $booking->id]) }}"
    data-dates-url="{{ route('treatment_reservations.booking.dates', ['id' => $booking->id]) }}"
>
    <div class="account-appointment-card__reschedule-fields">
        <div class="form-group">
            <label class="input-label" for="reschedule-date-{{ $booking->id }}">
                {{ trans('treatmentreservation::public.new_date') }}
            </label>
            <div class="modern-datepicker-wrap account-reschedule-datepicker">
                <i class="las la-calendar-alt" aria-hidden="true"></i>
                <input
                    id="reschedule-date-{{ $booking->id }}"
                    type="text"
                    name="appointment_date"
                    class="form-control modern-datepicker js-reschedule-date"
                    placeholder="{{ trans('treatmentreservation::public.choose_available_date') }}"
                    required
                    disabled
                    autocomplete="off"
                    data-min-date="{{ today()->toDateString() }}"
                    data-enable-dates=""
                    aria-describedby="reschedule-date-hint-{{ $booking->id }}"
                >
            </div>
            <p
                id="reschedule-date-hint-{{ $booking->id }}"
                class="help-block js-reschedule-dates-hint"
                data-default-text="{{ trans('treatmentreservation::public.open_dates_hint') }}"
                data-loading-text="{{ trans('treatmentreservation::public.loading_dates') }}"
                data-empty-text="{{ trans('treatmentreservation::public.no_available_dates') }}"
            >{{ trans('treatmentreservation::public.open_dates_hint') }}</p>
        </div>
        <div class="form-group">
            <label class="input-label" for="reschedule-time-{{ $booking->id }}">
                {{ trans('treatmentreservation::public.new_time') }}
            </label>
            <select
                id="reschedule-time-{{ $booking->id }}"
                name="appointment_time"
                class="form-control js-slot-select"
                required
                disabled
            >
                <option value="">{{ trans('treatmentreservation::public.select_date_first') }}</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">
        {{ trans('treatmentreservation::public.confirm_schedule') }}
    </button>
</form>
