@php
    $methodKey = $methodKey ?? 'chip_fpx';
    $icon = $icon ?? 'fpx';
    $faIcon = $faIcon ?? 'fa-university';
    $surchargeType = $surchargeType ?? 'flat';
    $methodPrefix = str_replace('_', '-', $methodKey);
    $isEnabled = old("{$methodKey}_enabled", array_get($settings, "{$methodKey}_enabled"));
@endphp

<article class="chip-method-card chip-method-card--{{ $icon }}">
    <header class="chip-method-card__hero">
        <div class="chip-method-card__icon" aria-hidden="true">
            <i class="fa {{ $faIcon }}"></i>
        </div>
        <div class="chip-method-card__titles">
            <h4 class="chip-method-card__title">{{ trans("setting::settings.chip.methods.{$icon}.title") }}</h4>
            <p class="chip-method-card__subtitle">{{ trans("setting::settings.chip.methods.{$icon}.subtitle") }}</p>
        </div>
        <div class="chip-method-card__toggle">
            {{ Form::checkbox("{$methodKey}_enabled", trans("setting::attributes.{$methodKey}_enabled"), trans('setting::settings.form.enable'), $errors, $settings) }}
        </div>
    </header>

    <div class="chip-method-card__body {{ $isEnabled ? '' : 'hide' }}" id="{{ $methodPrefix }}-fields">
        <div class="chip-method-card__fields">
            {{ Form::text("translatable[{$methodKey}_label]", trans("setting::attributes.translatable.{$methodKey}_label"), $errors, $settings, ['required' => true]) }}
            {{ Form::textarea("translatable[{$methodKey}_description]", trans("setting::attributes.translatable.{$methodKey}_description"), $errors, $settings, ['rows' => 2, 'required' => true]) }}

            @if ($surchargeType === 'percent')
                {{ Form::number("{$methodKey}_surcharge_percent", trans("setting::attributes.{$methodKey}_surcharge_percent"), $errors, $settings, [
                    'min' => 0,
                    'max' => 100,
                    'step' => 0.1,
                    'placeholder' => $icon === 'atome' ? '5.3' : ($icon === 'ewallet' ? '1.5' : '2.0'),
                ]) }}
                <p class="help-block text-muted">{{ trans("setting::settings.form.chip_surcharge_percent_help.{$icon}") }}</p>
            @else
                {{ Form::number("{$methodKey}_surcharge", trans("setting::attributes.{$methodKey}_surcharge"), $errors, $settings, [
                    'min' => 0,
                    'step' => 1,
                    'placeholder' => '100',
                ]) }}
                <p class="help-block text-muted">{{ trans('setting::settings.form.chip_surcharge_flat_help') }}</p>
            @endif

            {{ Form::text("{$methodKey}_whitelist", trans("setting::attributes.{$methodKey}_whitelist"), $errors, $settings, [
                'placeholder' => trans("setting::settings.form.chip_whitelist_placeholder.{$icon}"),
            ]) }}
            <p class="help-block text-muted">{{ trans('setting::settings.form.chip_whitelist_help') }}</p>
        </div>
    </div>
</article>
