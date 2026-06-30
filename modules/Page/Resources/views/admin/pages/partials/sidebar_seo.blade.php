@php
    $seoHasError = $errors->has('meta.meta_title')
        || $errors->has('meta.meta_description')
        || $errors->has('meta.og_image_id')
        || $errors->has('meta.meta_robots');
@endphp

<div class="page-editor-panel page-editor-panel--seo {{ $seoHasError ? 'page-editor-panel--error' : '' }}" data-page-panel>
    <button type="button" class="page-editor-panel__toggle" data-page-panel-toggle aria-expanded="true">
        <span class="page-editor-panel__title">
            <i class="fa fa-search" aria-hidden="true"></i>
            {{ trans('page::pages.tabs.seo') }}
            @if ($seoHasError)
                <i class="fa fa-exclamation-circle page-editor-panel__error-icon" aria-hidden="true"></i>
            @endif
        </span>
        <i class="fa fa-chevron-up page-editor-panel__chevron" aria-hidden="true"></i>
    </button>

    <div class="page-editor-panel__body" data-page-panel-body>
        <p class="page-editor-panel__intro">{{ trans('page::pages.form.seo_intro') }}</p>
        @include('page::admin.pages.partials.meta_fields', ['entity' => $page])
    </div>
</div>
