@if ($activities->isNotEmpty())
    <div class="tr-booking-activity-log">
        <h5>{{ trans('treatmentreservation::admin.activity.title') }}</h5>
        <ul class="tr-booking-activity-log__list">
            @foreach ($activities as $activity)
                <li class="tr-booking-activity-log__item">
                    <span class="tr-booking-activity-log__time">{{ $activity->created_at?->format('d M Y, H:i') }}</span>
                    <strong>{{ $activity->user?->full_name ?? trans('treatmentreservation::admin.activity.system') }}</strong>
                    <span>{{ $activity->summary() }}</span>
                    @if ($activity->action === \Modules\TreatmentReservation\Entities\TreatmentBookingActivity::ACTION_BEAUTICIAN_NOTES_UPDATED && $activity->to_value)
                        <p class="tr-booking-activity-log__notes">{{ $activity->to_value }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
