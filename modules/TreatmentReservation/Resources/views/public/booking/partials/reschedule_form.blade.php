<form
    class="account-appointment-card__reschedule hide js-reschedule-form"
    data-slots-url="{{ route('treatment_reservations.booking.slots', ['id' => $booking->id]) }}"
    data-dates-url="{{ route('treatment_reservations.booking.dates', ['id' => $booking->id]) }}"
>
    <div class="account-appointment-card__reschedule-fields">
        <div class="form-group">
            <label class="input-label">{{ trans('treatmentreservation::public.new_date') }}</label>
            <input
                type="date"
                name="appointment_date"
                class="form-control js-reschedule-date"
                required
                min="{{ today()->toDateString() }}"
                list="js-reschedule-dates-{{ $booking->id }}"
            >
            <datalist id="js-reschedule-dates-{{ $booking->id }}" class="js-reschedule-dates-list"></datalist>
            <p class="help-block js-reschedule-dates-hint hide">{{ trans('treatmentreservation::public.open_dates_hint') }}</p>
        </div>
        <div class="form-group">
            <label class="input-label">{{ trans('treatmentreservation::public.new_time') }}</label>
            <select name="appointment_time" class="form-control js-slot-select" required disabled>
                <option value="">{{ trans('treatmentreservation::public.loading_slots') }}</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">
        {{ trans('treatmentreservation::public.reschedule') }}
    </button>
</form>
