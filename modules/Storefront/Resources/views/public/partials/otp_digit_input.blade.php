@php
    $length = (int) ($length ?? 6);
    $model = $model ?? 'otp';
    $idPrefix = $idPrefix ?? 'otp';
    $useAlpine = $useAlpine ?? true;
    $title = $title ?? trans('user::auth.verification_code_title');
    $subtitle = $subtitle ?? trans('user::auth.verification_code_subtitle');
    $showPhone = $showPhone ?? false;
    $phoneDisplayId = $phoneDisplayId ?? null;
@endphp

<div class="otp-digit-input-wrap">
    <div class="otp-digit-input__header">
        <h3 class="otp-digit-input__title">{{ $title }}</h3>
        <p class="otp-digit-input__subtitle">
            {{ $subtitle }}
            @if ($showPhone && $useAlpine)
                <span class="otp-digit-input__phone" x-show="phone" x-text="phone"></span>
            @elseif ($showPhone && ! empty($phoneDisplayId))
                <span class="otp-digit-input__phone" id="{{ $phoneDisplayId }}"></span>
            @elseif ($showPhone && ! empty($phoneDisplay))
                <span class="otp-digit-input__phone">{{ $phoneDisplay }}</span>
            @endif
        </p>
    </div>

    <div
        class="otp-digit-input"
        @if ($useAlpine)
            x-data="otpDigitInput({ length: {{ $length }}, model: @js($model) })"
        @endif
        data-otp-digit-input
        data-otp-length="{{ $length }}"
    >
        <div class="otp-digit-input__cells" role="group" aria-label="{{ $title }}">
            @for ($i = 0; $i < $length; $i++)
                <input
                    type="text"
                    class="otp-digit-input__cell"
                    id="{{ $idPrefix }}-digit-{{ $i }}"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="1"
                    autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                    aria-label="{{ trans('user::auth.verification_code_digit', ['n' => $i + 1]) }}"
                    @if ($useAlpine)
                        x-model="cells[{{ $i }}]"
                        :class="{ 'is-filled': cells[{{ $i }}], 'is-active': activeIndex === {{ $i }} }"
                        @input="onCellInput({{ $i }}, $event)"
                        @keydown="onCellKeydown({{ $i }}, $event)"
                        @paste="onPaste($event)"
                        @focus="onCellFocus({{ $i }}, $event)"
                        @blur="onCellBlur()"
                    @endif
                >
            @endfor
        </div>
    </div>

    @if (! empty($hiddenInputId))
        <input type="hidden" id="{{ $hiddenInputId }}" value="" autocomplete="off">
    @endif
</div>
