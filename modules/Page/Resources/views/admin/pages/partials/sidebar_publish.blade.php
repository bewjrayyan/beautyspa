<div class="page-editor-panel page-editor-panel--publish">
    <h2 class="page-editor-panel__title">{{ trans('page::pages.form.publish') }}</h2>

    <div class="page-editor-panel__body">
        {{ Form::checkbox('is_active', trans('page::attributes.is_active'), trans('page::pages.form.enable_the_page'), $errors, $page) }}

        @if ($page->exists && $page->slug)
            <p class="page-editor-view-link">
                <a href="{{ $page->url() }}" target="_blank" rel="noopener noreferrer">
                    <i class="fa fa-external-link" aria-hidden="true"></i>
                    {{ trans('page::pages.form.view_page') }}
                </a>
            </p>
        @endif

        <div class="page-editor-panel__actions">
            <button type="submit" class="btn btn-primary btn-block" data-loading form="{{ $page->exists ? 'page-edit-form' : 'page-create-form' }}">
                {{ trans('admin::admin.buttons.save') }}
            </button>
        </div>
    </div>
</div>
