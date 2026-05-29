<script type="text/html" id="flash-sale-product-template">
    <article class="flash-sale-item flash-sale-product-panel" data-is-virtual="<%- product.is_virtual ? 1 : 0 %>">
        <header class="flash-sale-item__header">
            <span class="flash-sale-item__drag drag-handle" title="{{ trans('flashsale::flash_sales.products_tab.drag') }}">
                <i class="fa" aria-hidden="true">&#xf142;</i>
                <i class="fa" aria-hidden="true">&#xf142;</i>
            </span>

            <span class="flash-sale-item__index flash-sale-item__index-num"><%- productPanelNumber + 1 %></span>

            <h4 class="flash-sale-item__heading flash-sale-item__heading-text">
                <%- product.name ? product.name : '{{ trans('flashsale::flash_sales.products_tab.item_heading') }}' %>
            </h4>

            <% if (product.is_virtual) { %>
                <span class="flash-sale-item__badge flash-sale-virtual-badge">
                    <i class="fa fa-heart" aria-hidden="true"></i>
                    {{ trans('product::products.table.virtual_treatment') }}
                </span>
            <% } %>

            <% if (product.pivot.sold > 0) { %>
                <span class="flash-sale-item__sold">
                    <%- product.pivot.sold %> {{ trans('flashsale::flash_sales.products_tab.sold_label') }}
                </span>
            <% } %>

            <button type="button" class="flash-sale-item__remove delete-product-panel" title="{{ trans('flashsale::flash_sales.products_tab.remove') }}">
                <i class="fa fa-trash-o" aria-hidden="true"></i>
            </button>
        </header>

        <div class="flash-sale-item__body">
            <div class="flash-sale-item__field flash-sale-item__field--full">
                <label for="products-<%- productPanelNumber %>-product-id">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    {{ trans('flashsale::flash_sales.products_tab.field_treatment') }}
                    <span class="text-red">*</span>
                </label>

                <input type="hidden"
                    name="products[<%- productPanelNumber %>][name]"
                    class="form-control"
                    id="products-<%- productPanelNumber %>-name"
                    value="<%- product.name %>"
                >

                <select name="products[<%- productPanelNumber %>][product_id]"
                    class="form-control selectize prevent-creation flash-sale-product-select"
                    id="products-<%- productPanelNumber %>-product-id"
                    data-url="{{ route('admin.flash_sales.products.search') }}"
                    data-product-show-url="{{ url('admin/flash-sales/products') }}"
                    placeholder="{{ trans('flashsale::flash_sales.products_tab.search_placeholder') }}"
                >
                    <% if (product.id !== null && product.name !== null) { %>
                        <option value="<%- product.id %>"><%- product.name %></option>
                    <% } %>
                </select>
            </div>

            <div class="flash-sale-item__grid">
                <div class="flash-sale-item__field">
                    <label for="products-<%- productPanelNumber %>-campaign-end">
                        <i class="fa fa-calendar" aria-hidden="true"></i>
                        {{ trans('flashsale::attributes.end_date') }}
                        <span class="text-red">*</span>
                    </label>

                    <input
                        type="text"
                        name="products[<%- productPanelNumber %>][end_date]"
                        data-time
                        class="form-control datetime-picker"
                        id="products-<%- productPanelNumber %>-campaign-end"
                        value="<%- product.pivot.end_date %>"
                        placeholder="{{ trans('flashsale::flash_sales.products_tab.end_placeholder') }}"
                    >
                </div>

                <div class="flash-sale-item__field flash-sale-item__field--price">
                    <label for="products-<%- productPanelNumber %>-price">
                        <i class="fa fa-tag" aria-hidden="true"></i>
                        {{ trans('flashsale::flash_sales.products_tab.flash_price') }}
                        <span class="text-red">*</span>
                    </label>

                    <div class="input-group flash-sale-price-input">
                        <span class="input-group-addon">{{ currency_symbol(setting('default_currency')) }}</span>
                        <input type="number"
                            name="products[<%- productPanelNumber %>][price]"
                            class="form-control flash-sale-price-input__field"
                            id="products-<%- productPanelNumber %>-price"
                            value="<%- product.pivot.price.amount %>"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                        >
                    </div>

                    <p class="flash-sale-item__catalog-price help-block">
                        <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                        {{ trans('flashsale::flash_sales.products_tab.catalog_price') }}:
                        <strong class="flash-sale-item__catalog-price-value"><%- product.catalog_price_formatted || '—' %></strong>
                    </p>

                    <p class="flash-sale-item__price-help help-block">
                        {{ trans('flashsale::flash_sales.products_tab.flash_price_help') }}
                    </p>

                    <% if (product.has_options) { %>
                        <p class="flash-sale-item__price-note help-block">
                            <i class="fa fa-plus-circle" aria-hidden="true"></i>
                            {{ trans('flashsale::flash_sales.products_tab.flash_price_options_note') }}
                        </p>
                    <% } %>
                </div>

                <div class="flash-sale-item__field flash-sale-qty-group">
                    <label for="products-<%- productPanelNumber %>-qty">
                        <i class="fa fa-ticket" aria-hidden="true"></i>
                        {{ trans('flashsale::flash_sales.form.promotion_slots') }}
                        <span class="text-red">*</span>
                    </label>

                    <input type="number"
                        name="products[<%- productPanelNumber %>][qty]"
                        class="form-control flash-sale-qty-input"
                        id="products-<%- productPanelNumber %>-qty"
                        value="<%- product.pivot.qty %>"
                        min="0"
                        step="1"
                        placeholder="0"
                    >

                    <p class="flash-sale-item__slots-hint flash-sale-qty-help flash-sale-qty-help--virtual" style="display: none;">
                        <i class="fa fa-unlock-alt" aria-hidden="true"></i>
                        {{ trans('flashsale::flash_sales.form.qty_unlimited_virtual') }}
                    </p>
                </div>
            </div>
        </div>
    </article>
</script>
