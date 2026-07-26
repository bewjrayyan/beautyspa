<article class="consultation-question-row" data-question-row>
    <input type="hidden" name="questions[{{ $index }}][key]" value="{{ $question['key'] ?? '' }}">
    <div class="consultation-question-row__top">
        <span class="consultation-question-row__number" data-question-number>{{ is_numeric($index) ? $index + 1 : '' }}</span>
        <div class="form-group @error("questions.{$index}.label") has-error @enderror">
            <label>{{ trans('product::consultation_forms.question_label') }}</label>
            <input class="form-control" type="text" name="questions[{{ $index }}][label]" value="{{ $question['label'] ?? '' }}" maxlength="500">
        </div>
        <div class="form-group consultation-question-row__type">
            <label>{{ trans('product::consultation_forms.answer_type') }}</label>
            <select class="form-control" name="questions[{{ $index }}][type]" data-question-type>
                @foreach (['section', 'yes_no', 'text', 'textarea', 'date', 'select', 'checkbox', 'body_map'] as $type)
                    <option value="{{ $type }}" @selected(($question['type'] ?? 'text') === $type)>{{ trans("product::consultation_forms.types.{$type}") }}</option>
                @endforeach
            </select>
        </div>
        <div class="consultation-question-row__actions">
            <button class="btn btn-default" type="button" data-move-up aria-label="{{ trans('product::consultation_forms.move_up') }}"><i class="fa fa-arrow-up"></i></button>
            <button class="btn btn-default" type="button" data-move-down aria-label="{{ trans('product::consultation_forms.move_down') }}"><i class="fa fa-arrow-down"></i></button>
            <button class="btn btn-default text-red" type="button" data-remove-question aria-label="{{ trans('product::consultation_forms.remove_question') }}"><i class="fa fa-trash"></i></button>
        </div>
    </div>
    <div class="form-group consultation-question-row__options" data-question-options @if(!in_array($question['type'] ?? '', ['select', 'checkbox', 'body_map'])) hidden @endif>
        <label>{{ trans('product::consultation_forms.options') }}</label>
        <textarea class="form-control" name="questions[{{ $index }}][options]" rows="{{ max(4, min(24, substr_count((string) ($question['options'] ?? ''), "\n") + 1)) }}" placeholder="{{ trans('product::consultation_forms.options_placeholder') }}" data-question-options-input>{{ $question['options'] ?? '' }}</textarea>
        <small class="consultation-question-row__options-count" data-question-options-count data-count-template="{{ trans('product::consultation_forms.options_count', ['count' => '__COUNT__']) }}"></small>
    </div>
    <section class="consultation-question-row__body-map" data-question-body-map @if(($question['type'] ?? '') !== 'body_map') hidden @endif>
        <div class="consultation-question-row__body-map-heading">
            <span><i class="fa fa-street-view" aria-hidden="true"></i></span>
            <div>
                <strong>{{ trans('product::consultation_forms.body_map_title') }}</strong>
                <p>{{ trans('product::consultation_forms.body_map_help') }}</p>
            </div>
        </div>
        <div class="consultation-question-row__body-map-layout">
            @include('product::admin.consultation_forms.partials.body-map-preview')
            <div class="consultation-question-row__body-zones">
                <strong>{{ trans('product::consultation_forms.body_map_zones') }}</strong>
                <div data-question-body-zones></div>
            </div>
        </div>
    </section>
    <label class="consultation-question-row__required" data-question-required @if(($question['type'] ?? '') === 'section') hidden @endif><input type="hidden" name="questions[{{ $index }}][required]" value="0"><input type="checkbox" name="questions[{{ $index }}][required]" value="1" @checked((bool) ($question['required'] ?? false))> {{ trans('product::consultation_forms.required') }}</label>
</article>
