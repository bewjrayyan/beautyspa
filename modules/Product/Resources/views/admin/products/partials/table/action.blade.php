<div class="dropdown product-table-actions">
    <button
        type="button"
        class="btn btn-default btn-table-actions-toggle"
        aria-haspopup="true"
        aria-expanded="false"
        title="{{ trans('product::products.table.actions') }}"
        data-edit-url="{{ route('admin.products.edit', $product) }}"
        data-clone-url="{{ route('admin.products.clone', $product) }}"
        data-status-url="{{ route('admin.products.status', $product) }}"
        data-is-active="{{ $product->is_active ? '1' : '0' }}"
        data-view-url="{{ localized_url(locale(), $product->url()) }}"
        data-delete-id="{{ $product->id }}"
    >
        <span class="actions-dots" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </button>
</div>
