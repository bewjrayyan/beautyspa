<div class="page-editor-panel page-editor-panel--permalink" data-page-panel>
    <button type="button" class="page-editor-panel__toggle" data-page-panel-toggle aria-expanded="true">
        <span class="page-editor-panel__title">
            <i class="fa fa-link" aria-hidden="true"></i>
            {{ trans('page::pages.form.permalink') }}
        </span>
        <i class="fa fa-chevron-up page-editor-panel__chevron" aria-hidden="true"></i>
    </button>

    <div class="page-editor-panel__body" data-page-panel-body>
        @if ($page->exists)
            <div class="page-editor-permalink__preview" id="page-permalink-preview" data-base-url="{{ url('/') }}/">
                <span class="page-editor-permalink__base">{{ url('/') }}/</span><span class="page-editor-permalink__slug" id="page-permalink-slug-preview">{{ old('slug', $page->slug) }}</span>
            </div>

            <button type="button" class="page-editor-permalink__copy" id="page-permalink-copy" data-copied-label="{{ trans('page::pages.form.copied') }}">
                <i class="fa fa-copy" aria-hidden="true"></i>
                {{ trans('page::pages.form.copy_link') }}
            </button>
        @endif

        <p class="page-editor-permalink__hint">{{ trans('page::pages.form.permalink_hint') }}</p>

        {{ Form::text('slug', trans('page::attributes.slug'), $errors, $page, [
            'required' => true,
            'id' => 'page-slug-input',
            'placeholder' => trans('page::pages.form.slug_placeholder'),
        ]) }}
    </div>
</div>
