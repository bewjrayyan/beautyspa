@php
    $prefix = $prefix ?? 'cod';
    $enableLabel = $enableLabel ?? trans("setting::settings.form.enable_{$prefix}");
    $fieldsId = $fieldsId ?? str_replace('_', '-', $prefix) . '-fields';
    $hasInstructions = $hasInstructions ?? false;
@endphp

@component('setting::admin.settings.partials.settings-wrap')
    <div class="st-enable-card">
        {{ Form::checkbox("{$prefix}_enabled", trans("setting::attributes.{$prefix}_enabled"), $enableLabel, $errors, $settings, ['labelCol' => 0]) }}
    </div>

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-tag',
        'title' => trans('setting::settings.sections.display'),
        'class' => 'st-section--compact',
    ])
        @component('setting::admin.settings.partials.fields-grid')
            @slot('left')
                {{ Form::text("translatable[{$prefix}_label]", trans("setting::attributes.translatable.{$prefix}_label"), $errors, $settings, ['required' => true]) }}
            @endslot
            @slot('full')
                {{ Form::textarea("translatable[{$prefix}_description]", trans("setting::attributes.translatable.{$prefix}_description"), $errors, $settings, ['rows' => 3, 'required' => true]) }}
            @endslot
        @endcomponent
    @endcomponent

    @if ($hasInstructions)
        <div class="{{ old("{$prefix}_enabled", array_get($settings, "{$prefix}_enabled")) ? '' : 'hide' }}" id="{{ $fieldsId }}">
            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-file-text-o',
                'title' => trans('setting::settings.sections.instructions'),
            ])
                {{ Form::textarea("translatable[{$prefix}_instructions]", trans("setting::attributes.translatable.{$prefix}_instructions"), $errors, $settings, [
                    'rows' => 4,
                    'required' => true,
                    'placeholder' => 'Bank details & instructions to transfer&#10;(HTML tags supported)',
                ]) }}
            @endcomponent
        </div>
    @endif
@endcomponent
