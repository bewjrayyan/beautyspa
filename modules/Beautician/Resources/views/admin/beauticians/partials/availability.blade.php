@if ($beautician->exists && is_module_enabled('TreatmentReservation') && isset($workingHours, $blockedTimes, $availabilityDays))
    @php
        $enabledDaysCount = collect($availabilityDays)->filter(
            fn ($label, $index) => $workingHours->firstWhere('day_of_week', $index)
        )->count();

        $availabilityRoutes = [
            'hours' => route('admin.beauticians.portal.availability.hours', $beautician->id),
            'blocks' => route('admin.beauticians.portal.availability.blocks', $beautician->id),
        ];

        $destroyBlockUrl = function (int $blockId) use ($beautician) {
            return route('admin.beauticians.portal.availability.blocks.destroy', [
                'id' => $beautician->id,
                'blockId' => $blockId,
            ]);
        };
    @endphp

    <section
        class="bp-availability-admin tr-portal-profile-page"
        id="beautician-availability"
        data-availability-settings
        data-day-available="{{ trans('treatmentreservation::admin.availability.day_available') }}"
        data-day-off="{{ trans('treatmentreservation::admin.availability.day_off') }}"
    >
        <div class="bp-availability-admin__head">
            <div>
                <h3>{{ trans('beautician::beauticians.form.availability.title') }}</h3>
                <p>{{ trans('beautician::beauticians.form.availability.help') }}</p>
            </div>
            <a
                href="{{ route('admin.beauticians.portal.availability', $beautician->id) }}"
                class="btn btn-default btn-sm"
                target="_blank"
                rel="noopener noreferrer"
            >
                <i class="fa fa-external-link" aria-hidden="true"></i>
                {{ trans('beautician::beauticians.form.availability.open_full') }}
            </a>
        </div>

        <div class="bp-availability-summary">
            <div class="bp-availability-summary__item">
                <span>{{ trans('treatmentreservation::admin.availability.hero_days_available') }}</span>
                <strong>{{ trans('treatmentreservation::admin.availability.hero_days_available_value', ['count' => $enabledDaysCount]) }}</strong>
            </div>
            <div class="bp-availability-summary__item">
                <span>{{ trans('treatmentreservation::admin.availability.hero_upcoming_blocks') }}</span>
                <strong>{{ trans('treatmentreservation::admin.availability.hero_upcoming_blocks_value', ['count' => $blockedTimes->count()]) }}</strong>
            </div>
            <div class="bp-availability-summary__item">
                <span>{{ trans('treatmentreservation::admin.availability.hero_slot_duration') }}</span>
                <strong>{{ trans('treatmentreservation::admin.availability.hero_slot_duration_value') }}</strong>
            </div>
        </div>

        @include('treatmentreservation::admin.portal.partials.availability-settings', [
            'workingHours' => $workingHours,
            'blockedTimes' => $blockedTimes,
            'days' => $availabilityDays,
            'availabilityRoutes' => $availabilityRoutes,
            'destroyBlockUrl' => $destroyBlockUrl,
        ])
    </section>

    @push('styles')
        <style>
            .bp-availability-admin {
                margin: 20px 0 30px;
                padding: 20px;
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                background: #f9fafb;
            }
            .bp-availability-admin__head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 16px;
            }
            .bp-availability-admin__head h3 {
                margin: 0 0 4px;
                font-size: 18px;
                font-weight: 700;
                color: #111827;
            }
            .bp-availability-admin__head p {
                margin: 0;
                color: #6b7280;
                font-size: 13px;
            }
            .bp-availability-summary {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 12px;
                margin-bottom: 16px;
            }
            .bp-availability-summary__item {
                padding: 14px 16px;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                background: #fff;
            }
            .bp-availability-summary__item span {
                display: block;
                margin-bottom: 4px;
                color: #6b7280;
                font-size: 12px;
            }
            .bp-availability-summary__item strong {
                color: #111827;
                font-size: 14px;
            }
            .bp-availability-admin .bp-card {
                margin-bottom: 14px;
            }
            @media (max-width: 991px) {
                .bp-availability-summary { grid-template-columns: 1fr; }
                .bp-availability-admin__head { flex-direction: column; }
            }
        </style>
    @endpush
@endif
