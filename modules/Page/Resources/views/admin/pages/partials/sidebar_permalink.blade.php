<div class="page-editor-panel page-editor-panel--permalink">
    <h2 class="page-editor-panel__title">{{ trans('page::pages.form.permalink') }}</h2>

    <div class="page-editor-panel__body">
        @if ($page->exists)
            <p class="page-editor-permalink__sample">
                <span class="page-editor-permalink__base">{{ url('/') }}/</span>
            </p>
        @endif

        {{ Form::text('slug', trans('page::attributes.slug'), $errors, $page, ['required' => true]) }}
    </div>
</div>
