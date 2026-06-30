@php
    $isEdit = $page->exists;
    $isActive = (bool) old('is_active', $page->is_active);
@endphp

<header class="page-editor-hub">
    <div class="page-editor-hub__main">
        <div class="page-editor-hub__icon" aria-hidden="true">
            <i class="fa fa-file-text-o"></i>
        </div>

        <div class="page-editor-hub__copy">
            <span class="page-editor-hub__eyebrow">
                {{ $isEdit ? trans('page::pages.form.hub_eyebrow_edit') : trans('page::pages.form.hub_eyebrow_create') }}
            </span>

            <h1 class="page-editor-hub__title" id="page-editor-hub-title" data-default-title="{{ $isEdit ? $page->name : trans('page::pages.form.new_page') }}">
                {{ old('name', $page->name) ?: ($isEdit ? $page->name : trans('page::pages.form.new_page')) }}
            </h1>

            <p class="page-editor-hub__lead">{{ trans('page::pages.form.hub_lead') }}</p>
        </div>
    </div>

    <div class="page-editor-hub__meta">
        <span class="page-editor-hub__badge page-editor-hub__badge--{{ $isActive ? 'published' : 'draft' }}" id="page-editor-status-badge" data-published-label="{{ trans('page::pages.form.status_published') }}" data-draft-label="{{ trans('page::pages.form.status_draft') }}">
            <i class="fa fa-circle" aria-hidden="true"></i>
            {{ $isActive ? trans('page::pages.form.status_published') : trans('page::pages.form.status_draft') }}
        </span>

        @if ($isEdit && $page->updated_at)
            <span class="page-editor-hub__updated">
                <i class="fa fa-clock-o" aria-hidden="true"></i>
                {{ trans('page::pages.form.last_updated', ['date' => $page->updated_at->timezone(config('app.timezone'))->format('d M Y, H:i')]) }}
            </span>
        @endif

        @if ($isEdit && $page->slug)
            <a href="{{ $page->url() }}" class="page-editor-hub__view" target="_blank" rel="noopener noreferrer">
                <i class="fa fa-external-link" aria-hidden="true"></i>
                {{ trans('page::pages.form.view_page') }}
            </a>
        @endif
    </div>
</header>
