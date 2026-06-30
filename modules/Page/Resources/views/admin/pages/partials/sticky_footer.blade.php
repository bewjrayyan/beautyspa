<div class="page-editor-sticky-footer">
    <div class="page-editor-sticky-footer__inner">
        <p class="page-editor-sticky-footer__hint">
            <i class="fa fa-info-circle" aria-hidden="true"></i>
            {{ trans('page::pages.form.save_hint') }}
        </p>

        <div class="page-editor-sticky-footer__actions">
            @if ($page->exists && $page->slug)
                <a
                    href="{{ $page->url() }}"
                    class="btn btn-default"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="fa fa-external-link" aria-hidden="true"></i>
                    {{ trans('page::pages.form.view_page') }}
                </a>
            @endif

            <button type="submit" class="btn btn-primary" data-loading form="{{ $page->exists ? 'page-edit-form' : 'page-create-form' }}">
                <i class="fa fa-save" aria-hidden="true"></i>
                {{ trans('admin::admin.buttons.save') }}
            </button>
        </div>
    </div>
</div>
