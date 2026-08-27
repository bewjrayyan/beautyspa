@php
    $availabilityRoutes = $availabilityRoutes ?? [
        'hours' => route('admin.treatment_reservations.portal.availability.hours'),
        'blocks' => route('admin.treatment_reservations.portal.availability.blocks'),
    ];
    $destroyBlockUrl = $destroyBlockUrl ?? function (int $blockId) {
        return route('admin.treatment_reservations.portal.availability.blocks.destroy', $blockId);
    };
@endphp

<form method="POST" action="{{ $availabilityRoutes['hours'] }}" id="tr-availability-hours-form">
    @csrf
    @method('PUT')

    <div class="bp-card">
        <div class="bp-card-header">
            <h3>{{ trans('treatmentreservation::admin.availability.weekly_hours') }}</h3>
            <p>{{ trans('treatmentreservation::admin.availability.weekly_hours_help') }}</p>
        </div>
        <div class="bp-card-body">
            <div class="bp-availability-days">
                @foreach ($days as $dayIndex => $dayLabel)
                    @php
                        $row = $workingHours->firstWhere('day_of_week', $dayIndex);
                        $isEnabled = (bool) $row;
                    @endphp
                    <div class="bp-availability-day {{ $isEnabled ? 'is-enabled' : '' }}" data-availability-day>
                        <input type="hidden" name="hours[{{ $dayIndex }}][day_of_week]" value="{{ $dayIndex }}">

                        <div class="bp-availability-day__label">
                            <span class="bp-availability-day__name">{{ $dayLabel }}</span>
                            <span class="bp-availability-day__status">
                                {{ $isEnabled
                                    ? trans('treatmentreservation::admin.availability.day_available')
                                    : trans('treatmentreservation::admin.availability.day_off') }}
                            </span>
                        </div>

                        <label class="bp-switch bp-availability-day__switch">
                            <input
                                type="checkbox"
                                name="hours[{{ $dayIndex }}][enabled]"
                                value="1"
                                class="bp-availability-day__toggle"
                                {{ $isEnabled ? 'checked' : '' }}
                            >
                            <span class="bp-switch-slider"></span>
                        </label>

                        <div class="bp-availability-day__times">
                            <div class="tr-avail-time-field">
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                <input
                                    type="text"
                                    name="hours[{{ $dayIndex }}][start_time]"
                                    class="form-control bp-input bp-availability-day__time"
                                    value="{{ $row ? \Illuminate\Support\Str::substr($row->start_time, 0, 5) : '10:00' }}"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    data-modern-time
                                    aria-label="{{ trans('treatmentreservation::admin.availability.start') }}"
                                    {{ $isEnabled ? '' : 'disabled' }}
                                >
                            </div>
                            <span class="bp-availability-day__sep">–</span>
                            <div class="tr-avail-time-field">
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                <input
                                    type="text"
                                    name="hours[{{ $dayIndex }}][end_time]"
                                    class="form-control bp-input bp-availability-day__time"
                                    value="{{ $row ? \Illuminate\Support\Str::substr($row->end_time, 0, 5) : '18:00' }}"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    data-modern-time
                                    aria-label="{{ trans('treatmentreservation::admin.availability.end') }}"
                                    {{ $isEnabled ? '' : 'disabled' }}
                                >
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bp-form-actions">
                <button type="submit" class="btn btn-primary">
                    {{ trans('treatmentreservation::admin.availability.save_hours') }}
                </button>
            </div>
        </div>
    </div>
</form>

<div class="bp-card">
    <div class="bp-card-header">
        <h3>{{ trans('treatmentreservation::admin.availability.blocked_times') }}</h3>
        <p>{{ trans('treatmentreservation::admin.availability.blocked_times_help') }}</p>
    </div>
    <div class="bp-card-body">
        <form method="POST" action="{{ $availabilityRoutes['blocks'] }}" class="bp-block-form">
            @csrf

            <div class="bp-block-form__grid">
                <div class="form-group">
                    <label for="block_date">{{ trans('treatmentreservation::admin.availability.block_date') }}</label>
                    <input
                        type="date"
                        name="block_date"
                        id="block_date"
                        class="form-control bp-input"
                        required
                        min="{{ today()->toDateString() }}"
                    >
                </div>
                <div class="form-group">
                    <label for="block_start_time">{{ trans('treatmentreservation::admin.availability.start') }}</label>
                    <div class="tr-avail-time-field">
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <input
                            type="text"
                            name="start_time"
                            id="block_start_time"
                            class="form-control bp-input"
                            inputmode="numeric"
                            autocomplete="off"
                            data-modern-time
                            required
                        >
                    </div>
                </div>
                <div class="form-group">
                    <label for="block_end_time">{{ trans('treatmentreservation::admin.availability.end') }}</label>
                    <div class="tr-avail-time-field">
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <input
                            type="text"
                            name="end_time"
                            id="block_end_time"
                            class="form-control bp-input"
                            inputmode="numeric"
                            autocomplete="off"
                            data-modern-time
                            required
                        >
                    </div>
                </div>
                <div class="form-group">
                    <label for="block_note">{{ trans('treatmentreservation::admin.availability.note') }}</label>
                    <input type="text" name="note" id="block_note" class="form-control bp-input" maxlength="255">
                </div>
            </div>

            <div class="bp-block-form__actions">
                <button type="submit" class="btn btn-default">
                    <i class="fa fa-plus"></i>
                    {{ trans('treatmentreservation::admin.availability.add_block') }}
                </button>
            </div>
        </form>

        @if ($blockedTimes->isNotEmpty())
            <div class="bp-block-list">
                @foreach ($blockedTimes as $block)
                    <article class="bp-block-item">
                        <div class="bp-block-item__main">
                            <strong>{{ $block->block_date->format('d M Y') }}</strong>
                            <span class="bp-block-item__time">
                                {{ \Illuminate\Support\Str::substr($block->start_time, 0, 5) }}
                                –
                                {{ \Illuminate\Support\Str::substr($block->end_time, 0, 5) }}
                            </span>
                            @if ($block->note)
                                <span class="bp-block-item__note">{{ $block->note }}</span>
                            @endif
                        </div>
                        <form
                            method="POST"
                            action="{{ $destroyBlockUrl($block->id) }}"
                            onsubmit="return confirm(@js(trans('treatmentreservation::admin.availability.remove_confirm')));"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fa fa-trash"></i>
                                {{ trans('treatmentreservation::admin.availability.remove') }}
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>
        @else
            <p class="bp-empty-state">{{ trans('treatmentreservation::admin.availability.no_blocks') }}</p>
        @endif
    </div>
</div>
