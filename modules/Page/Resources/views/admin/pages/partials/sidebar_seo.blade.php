@php
    $seoHasError = $errors->has('meta.meta_title')
        || $errors->has('meta.meta_description')
        || $errors->has('meta.og_image_id')
        || $errors->has('meta.meta_robots');
@endphp

<div class="page-editor-panel page-editor-panel--seo {{ $seoHasError ? 'page-editor-panel--error' : '' }}">
    <h2 class="page-editor-panel__title">
        {{ trans('page::pages.tabs.seo') }}
        @if ($seoHasError)
            <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
        @endif
    </h2>

    <div class="page-editor-panel__body">
        @include('page::admin.pages.partials.meta_fields', ['entity' => $page])
    </div>
</div>
