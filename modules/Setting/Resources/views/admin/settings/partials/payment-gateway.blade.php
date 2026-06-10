@php
    $prefix = $prefix ?? 'paypal';
    $fieldsId = $fieldsId ?? "{$prefix}-fields";
    $enableLabel = $enableLabel ?? trans("setting::settings.form.enable_{$prefix}");
    $hasSandbox = $hasSandbox ?? true;
    $errors = $errors ?? request()->session()->get('errors') ?? new \Illuminate\Support\ViewErrorBag();
    $settings = $settings ?? setting()->all();
@endphp

<div class="row st-gateway">
    <div class="col-md-12">
        <div class="st-enable-card">
            {{ Form::checkbox("{$prefix}_enabled", ' ', $enableLabel, $errors, $settings, ['labelCol' => 0]) }}
        </div>

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-tag',
            'title' => trans('setting::settings.sections.display'),
            'class' => 'st-section--compact',
        ])
            @component('setting::admin.settings.partials.fields-grid')
                @slot('left')
                    {{ Form::text("translatable[{$prefix}_label]", trans("setting::attributes.translatable.{$prefix}_label"), $errors, $settings, ['required' => true]) }}
                    @if ($hasSandbox)
                        {{ Form::checkbox("{$prefix}_test_mode", trans("setting::attributes.{$prefix}_test_mode"), trans('setting::settings.form.use_sandbox_for_test_payments'), $errors, $settings) }}
                    @endif
                    {{ $displayLeft ?? '' }}
                @endslot
                @slot('right')
                    {{ $displayRight ?? '' }}
                @endslot
                @slot('full')
                    {{ Form::textarea("translatable[{$prefix}_description]", trans("setting::attributes.translatable.{$prefix}_description"), $errors, $settings, ['rows' => 3, 'required' => true]) }}
                    {{ $display ?? '' }}
                @endslot
            @endcomponent
        @endcomponent

        <div class="{{ old("{$prefix}_enabled", array_get($settings, "{$prefix}_enabled")) ? '' : 'hide' }}" id="{{ $fieldsId }}">
            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-key',
                'title' => trans('setting::settings.sections.credentials'),
            ])
                <div class="st-fields-grid st-fields-grid--credentials">
                    {{ $credentials ?? '' }}
                </div>
            @endcomponent
        </div>
    </div>
</div>
