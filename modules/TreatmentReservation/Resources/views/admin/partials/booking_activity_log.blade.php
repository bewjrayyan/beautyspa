@if ($activities->isNotEmpty())
    <div class="tr-booking-activity-log">
        <header class="tr-booking-activity-log__header">
            <div class="tr-booking-activity-log__heading">
                <span class="tr-booking-activity-log__heading-icon" aria-hidden="true">
                    <i class="fa fa-history"></i>
                </span>
                <div>
                    <span class="tr-booking-activity-log__eyebrow">
                        {{ trans('treatmentreservation::admin.activity.eyebrow') }}
                    </span>
                    <h3>{{ trans('treatmentreservation::admin.activity.title') }}</h3>
                    <p>{{ trans('treatmentreservation::admin.activity.subtitle') }}</p>
                </div>
            </div>
            <span class="tr-booking-activity-log__count">
                {{ trans_choice('treatmentreservation::admin.activity.events', $activities->count(), ['count' => number_format($activities->count())]) }}
            </span>
        </header>

        <ul class="tr-booking-activity-log__list">
            @foreach ($activities as $activity)
                @php
                    $activityMeta = match ($activity->action) {
                        \Modules\TreatmentReservation\Entities\TreatmentBookingActivity::ACTION_STATUS_CHANGED => ['exchange', 'blue'],
                        \Modules\TreatmentReservation\Entities\TreatmentBookingActivity::ACTION_BEAUTICIAN_NOTES_UPDATED => ['sticky-note-o', 'violet'],
                        \Modules\TreatmentReservation\Entities\TreatmentBookingActivity::ACTION_TREATMENT_WORK_LOG_UPDATED => ['check-square-o', 'green'],
                        \Modules\TreatmentReservation\Entities\TreatmentBookingActivity::ACTION_WHATSAPP_SENT,
                        \Modules\TreatmentReservation\Entities\TreatmentBookingActivity::ACTION_REMINDER_SENT,
                        \Modules\TreatmentReservation\Entities\TreatmentBookingActivity::ACTION_BEAUTICIAN_REMINDER_SENT => ['commenting-o', 'teal'],
                        \Modules\TreatmentReservation\Entities\TreatmentBookingActivity::ACTION_CREATED => ['plus', 'green'],
                        default => ['pencil', 'slate'],
                    };
                    $actorName = $activity->user?->full_name ?? trans('treatmentreservation::admin.activity.system');
                    $actorInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($actorName, 0, 1));
                    $actionLabel = $activity->actionLabel();
                    $summary = $activity->summary();
                    $showSummary = filled($summary) && $summary !== $actionLabel;
                @endphp

                <li class="tr-booking-activity-log__item tr-booking-activity-log__item--{{ $activityMeta[1] }}">
                    <span class="tr-booking-activity-log__marker" aria-hidden="true">
                        <i class="fa fa-{{ $activityMeta[0] }}"></i>
                    </span>

                    <article class="tr-booking-activity-log__event">
                        <div class="tr-booking-activity-log__event-head">
                            <div class="tr-booking-activity-log__event-copy">
                                <strong>{{ $actionLabel }}</strong>
                                @if ($showSummary)
                                    <p>{{ $summary }}</p>
                                @endif
                            </div>
                            <time class="tr-booking-activity-log__time" datetime="{{ $activity->created_at?->toIso8601String() }}">
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                {{ $activity->created_at?->translatedFormat('d M Y, H:i') }}
                            </time>
                        </div>

                        <div class="tr-booking-activity-log__actor">
                            <span class="tr-booking-activity-log__avatar" aria-hidden="true">{{ $actorInitial }}</span>
                            <span>
                                <small>{{ trans('treatmentreservation::admin.activity.performed_by') }}</small>
                                <strong>{{ $actorName }}</strong>
                            </span>
                        </div>

                        @if ($activity->action === \Modules\TreatmentReservation\Entities\TreatmentBookingActivity::ACTION_BEAUTICIAN_NOTES_UPDATED && $activity->to_value)
                            <div class="tr-booking-activity-log__notes">
                                <i class="fa fa-quote-left" aria-hidden="true"></i>
                                <div>
                                    <small>{{ trans('treatmentreservation::admin.activity.note_detail') }}</small>
                                    <p>{{ $activity->to_value }}</p>
                                </div>
                            </div>
                        @endif
                    </article>
                </li>
            @endforeach
        </ul>
    </div>
@endif
