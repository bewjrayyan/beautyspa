@php
    $selectedAreas = collect($selectedAreas ?? [])->filter()->values()->all();
    $interactive = $interactive ?? false;
    $markerPositions = \Modules\Account\Support\ConsultationBodyMap::positions();
@endphp

<div class="consultation-body-map__figure">
    <div class="consultation-body-map__anatomy-labels" aria-hidden="true">
        <span>{{ trans('account::consultation.medical.male_front') }}</span>
        <span>{{ trans('account::consultation.medical.male_back') }}</span>
        <span>{{ trans('account::consultation.medical.female_front') }}</span>
        <span>{{ trans('account::consultation.medical.female_back') }}</span>
    </div>
    <div class="consultation-body-map__canvas">
        <img src="{{ $imageSource ?? asset('images/consultation/anatomi_badan_depan_belakang.svg') }}" alt="{{ trans('account::consultation.medical.body_map_alt') }}">
        <div class="consultation-body-map__markers" aria-hidden="true">
            @foreach (($options ?? []) as $optionIndex => $option)
                @if ($interactive || in_array($option, $selectedAreas, true))
                    @foreach (($markerPositions[$option] ?? []) as [$left, $top])
                        <span
                            class="consultation-body-marker @if(in_array($option, $selectedAreas, true)) is-visible @endif"
                            data-body-marker="{{ sha1($option) }}"
                            style="left: {{ $left }}%; top: {{ $top }}%;"
                        >{{ $optionIndex + 1 }}</span>
                    @endforeach
                @endif
            @endforeach
        </div>
        <div class="consultation-body-map__callouts" aria-hidden="true">
            @foreach (($options ?? []) as $optionIndex => $option)
                @if ($interactive || in_array($option, $selectedAreas, true))
                    @php
                        $positions = $markerPositions[$option] ?? [];
                        $calloutPosition = collect($positions)->first(
                            fn (array $position): bool => $position[0] >= 55 && $position[0] <= 88
                        ) ?: collect($positions)->first();
                    @endphp
                    @if ($calloutPosition)
                        @php [$calloutLeft, $calloutTop] = $calloutPosition; @endphp
                        <span
                            class="consultation-body-callout {{ $calloutLeft > 72 ? 'is-left' : 'is-right' }} @if(in_array($option, $selectedAreas, true)) is-visible @endif"
                            data-body-marker="{{ sha1($option) }}"
                            style="left: {{ $calloutLeft }}%; top: {{ $calloutTop }}%;"
                        >
                            <i aria-hidden="true"></i>
                            <b><em>{{ $optionIndex + 1 }}</em>{{ $option }}</b>
                        </span>
                    @endif
                @endif
            @endforeach
        </div>
    </div>
    <small class="consultation-body-map__figure-help"><i class="las la-map-marker" aria-hidden="true"></i> {{ trans('account::consultation.medical.marker_help') }}</small>
</div>
