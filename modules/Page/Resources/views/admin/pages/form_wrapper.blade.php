<div class="page-editor">
    <div class="page-editor__canvas">
        {{ $contents }}
    </div>

    <aside class="page-editor__sidebar">
        @include('page::admin.pages.partials.sidebar_publish', ['page' => $page, 'buttonOffset' => $buttonOffset])

        @if ($page->slug ?? false)
            @include('page::admin.pages.partials.sidebar_permalink', ['page' => $page])
        @endif

        @include('page::admin.pages.partials.sidebar_seo', ['page' => $page])
    </aside>
</div>
