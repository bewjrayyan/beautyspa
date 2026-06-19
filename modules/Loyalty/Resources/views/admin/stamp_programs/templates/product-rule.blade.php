<script type="text/html" id="stamp-program-product-template">
    <article class="loyalty-stamp-product-panel" data-panel-index="<%- panelIndex %>">
        <header class="loyalty-stamp-product-panel__header">
            <span class="loyalty-stamp-product-panel__index"><%- panelIndex + 1 %></span>
            <h4 class="loyalty-stamp-product-panel__title">
                <%- item.name ? item.name : '{{ trans('loyalty::stamp_programs.form.products.item_heading') }}' %>
            </h4>
            <% if (item.is_virtual) { %>
                <span class="loyalty-stamp-product-panel__badge">
                    <i class="fa fa-heart" aria-hidden="true"></i>
                    {{ trans('product::products.table.virtual_treatment') }}
                </span>
            <% } %>
            <button type="button" class="loyalty-stamp-product-panel__remove" title="{{ trans('loyalty::stamp_programs.form.products.remove') }}">
                <i class="fa fa-trash-o" aria-hidden="true"></i>
            </button>
        </header>

        <div class="loyalty-stamp-product-panel__body">
            <div class="loyalty-stamp-product-panel__field loyalty-stamp-product-panel__field--full">
                <label for="eligible-products-<%- panelIndex %>-product-id">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    {{ trans('loyalty::stamp_programs.form.products.field_product') }}
                    <span class="text-red">*</span>
                </label>

                <select
                    name="eligible_products[<%- panelIndex %>][product_id]"
                    class="form-control selectize prevent-creation loyalty-stamp-product-select"
                    id="eligible-products-<%- panelIndex %>-product-id"
                    data-url="{{ route('admin.loyalty.stamp_programs.products.search') }}"
                    placeholder="{{ trans('loyalty::stamp_programs.form.products.search_placeholder') }}"
                >
                    <% if (item.product_id) { %>
                        <option value="<%- item.product_id %>" selected><%- item.name || ('#' + item.product_id) %></option>
                    <% } %>
                </select>
            </div>

            <div class="loyalty-stamp-product-panel__config"></div>
        </div>
    </article>
</script>
