<div class="page-editor-panel page-editor-panel--publish" data-page-panel>
    <button type="button" class="page-editor-panel__toggle" data-page-panel-toggle aria-expanded="true">
        <span class="page-editor-panel__title">
            <i class="fa fa-bullhorn" aria-hidden="true"></i>
            {{ trans('page::pages.form.publish') }}
        </span>
        <i class="fa fa-chevron-up page-editor-panel__chevron" aria-hidden="true"></i>
    </button>

    <div class="page-editor-panel__body" data-page-panel-body>
        <div class="page-editor-publish-status">
            <span class="page-editor-publish-status__label">{{ trans('page::pages.form.visibility') }}</span>
            {{ Form::checkbox('is_active', trans('page::attributes.is_active'), trans('page::pages.form.enable_the_page'), $errors, $page) }}
            <p class="page-editor-publish-status__hint">{{ trans('page::pages.form.visibility_hint') }}</p>
        </div>

        @if ($page->exists && $page->slug)
            <p class="page-editor-view-link">
                <a href="{{ $page->url() }}" target="_blank" rel="noopener noreferrer">
                    <i class="fa fa-external-link" aria-hidden="true"></i>
                    {{ trans('page::pages.form.view_page') }}
                </a>
            </p>
        @endif
    </div>
</div>
