@php
    $pickerId = $pickerId ?? 'tr-manual-booking-beautician';
    $pickerOptions = $pickerOptions ?? [];
    $selectedId = isset($selectedId) ? (int) $selectedId : null;
    $placeholder = $placeholder ?? trans('storefront::checkout.select_beautician');
    $selectedBeautician = collect($pickerOptions)->firstWhere('id', $selectedId);
@endphp

<div class="tr-beautician-picker" data-placeholder="{{ $placeholder }}">
    <select
        id="{{ $pickerId }}"
        name="beautician_id"
        class="tr-beautician-picker__native"
        required
        tabindex="-1"
        aria-hidden="true"
    >
        <option value="">{{ $placeholder }}</option>
        @foreach ($pickerOptions as $beautician)
            <option
                value="{{ $beautician['id'] }}"
                {{ $selectedId === (int) $beautician['id'] ? 'selected' : '' }}
            >
                {{ $beautician['name'] }}
            </option>
        @endforeach
    </select>

    <button
        type="button"
        class="tr-beautician-picker__card{{ $selectedBeautician ? '' : ' is-placeholder' }}"
        aria-haspopup="listbox"
        aria-expanded="false"
        aria-labelledby="{{ $pickerId }}-label"
    >
        <span class="tr-beautician-picker__selected" @if (! $selectedBeautician) hidden @endif>
            @if ($selectedBeautician)
                @if (! empty($selectedBeautician['profile_image']))
                    <img
                        src="{{ $selectedBeautician['profile_image'] }}"
                        alt="{{ $selectedBeautician['name'] }}"
                        class="tr-beautician-picker__avatar tr-beautician-picker__avatar--photo"
                    >
                @else
                    <span
                        class="tr-beautician-picker__avatar"
                        style="background-color: {{ $selectedBeautician['profile_color'] ?? '#6366f1' }}"
                    >
                        {{ strtoupper(substr($selectedBeautician['name'], 0, 1)) }}
                    </span>
                @endif
                <span class="tr-beautician-picker__text">
                    <span class="tr-beautician-picker__name">{{ $selectedBeautician['name'] }}</span>
                    @if (! empty($selectedBeautician['job_title']))
                        <span class="tr-beautician-picker__title">{{ $selectedBeautician['job_title'] }}</span>
                    @endif
                </span>
            @endif
        </span>

        <span class="tr-beautician-picker__placeholder" @if ($selectedBeautician) hidden @endif>
            {{ $placeholder }}
        </span>

        <i class="fa fa-angle-down tr-beautician-picker__chevron" aria-hidden="true"></i>
    </button>

    <ul class="tr-beautician-picker__options" role="listbox" hidden>
        @foreach ($pickerOptions as $beautician)
            <li role="presentation">
                <button
                    type="button"
                    class="tr-beautician-picker__option{{ $selectedId === (int) $beautician['id'] ? ' is-active' : '' }}"
                    data-beautician-id="{{ $beautician['id'] }}"
                    data-beautician-name="{{ $beautician['name'] }}"
                    data-beautician-title="{{ $beautician['job_title'] ?? '' }}"
                    data-beautician-color="{{ $beautician['profile_color'] ?? '#6366f1' }}"
                    data-beautician-image="{{ $beautician['profile_image'] ?? '' }}"
                    role="option"
                    aria-selected="{{ $selectedId === (int) $beautician['id'] ? 'true' : 'false' }}"
                >
                    @if (! empty($beautician['profile_image']))
                        <img
                            src="{{ $beautician['profile_image'] }}"
                            alt="{{ $beautician['name'] }}"
                            class="tr-beautician-picker__avatar tr-beautician-picker__avatar--photo"
                        >
                    @else
                        <span
                            class="tr-beautician-picker__avatar"
                            style="background-color: {{ $beautician['profile_color'] ?? '#6366f1' }}"
                        >
                            {{ strtoupper(substr($beautician['name'], 0, 1)) }}
                        </span>
                    @endif
                    <span class="tr-beautician-picker__text">
                        <span class="tr-beautician-picker__name">{{ $beautician['name'] }}</span>
                        @if (! empty($beautician['job_title']))
                            <span class="tr-beautician-picker__title">{{ $beautician['job_title'] }}</span>
                        @endif
                    </span>
                </button>
            </li>
        @endforeach
    </ul>
</div>
